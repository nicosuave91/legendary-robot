import type {
  WorkflowBuilderOperator,
  WorkflowBuilderState,
  WorkflowBuilderStepState,
  WorkflowBuilderStepType,
  WorkflowTemplatePreset,
  WorkflowTriggerFilter
} from '@/features/workflow-builder/workflow-builder-types'

export const WORKFLOW_STEP_TYPE_LABELS: Record<WorkflowBuilderStepType, string> = {
  condition: 'Condition',
  wait: 'Wait',
  create_client_note: 'Create client note',
  send_sms: 'Send SMS',
  send_email: 'Send email'
}

export const WORKFLOW_RUNTIME_CLIENT_SUBJECT_TYPES = ['client', 'application'] as const

export const WORKFLOW_OPERATOR_OPTIONS: Array<{ value: WorkflowBuilderOperator; label: string }> = [
  { value: 'eq', label: 'Equals' },
  { value: 'neq', label: 'Does not equal' },
  { value: 'gte', label: 'Greater than or equal' },
  { value: 'lte', label: 'Less than or equal' },
  { value: 'contains', label: 'Contains' },
  { value: 'exists', label: 'Exists' }
]

function safeId(prefix: string) {
  if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
    return `${prefix}-${crypto.randomUUID()}`
  }

  return `${prefix}-${Math.random().toString(36).slice(2, 10)}`
}

export function createWorkflowFilter(partial?: Partial<WorkflowTriggerFilter>): WorkflowTriggerFilter {
  return {
    id: safeId('wf-filter'),
    fact: partial?.fact ?? '',
    operator: partial?.operator ?? 'eq',
    value: partial?.value ?? ''
  }
}

export function createWorkflowStep(type: WorkflowBuilderStepType): WorkflowBuilderStepState {
  if (type === 'condition') {
    return {
      id: safeId('wf-step'),
      type,
      definition: { fact: '', operator: 'eq', value: '' }
    }
  }

  if (type === 'wait') {
    return {
      id: safeId('wf-step'),
      type,
      definition: { durationMinutes: '60' }
    }
  }

  if (type === 'create_client_note') {
    return {
      id: safeId('wf-step'),
      type,
      definition: { title: 'Follow-up', bodyTemplate: 'This workflow matched a client lifecycle event.' }
    }
  }

  if (type === 'send_sms') {
    return {
      id: safeId('wf-step'),
      type,
      definition: { bodyTemplate: 'Thanks for the update. We will follow up shortly.' }
    }
  }

  return {
    id: safeId('wf-step'),
    type,
    definition: {
      subjectTemplate: 'Workflow update',
      bodyTemplate: 'This automated email was triggered by a published workflow.'
    }
  }
}

export function parseWorkflowContractToBuilderState(
  triggerDefinition: Record<string, unknown> | null | undefined,
  stepsDefinition: Array<Record<string, unknown>> | null | undefined
): WorkflowBuilderState {
  const filters = Array.isArray(triggerDefinition?.filters)
    ? triggerDefinition!.filters.map((item) => {
      const filter = item as Record<string, unknown>
      return createWorkflowFilter({
        fact: String(filter.fact ?? ''),
        operator: (filter.operator as WorkflowBuilderOperator | undefined) ?? 'eq',
        value: String(filter.value ?? '')
      })
    })
    : []

  const steps = Array.isArray(stepsDefinition) && stepsDefinition.length
    ? stepsDefinition.map((step) => {
      const type = String(step.type ?? 'create_client_note') as WorkflowBuilderStepType
      const definition = (step.definition ?? {}) as Record<string, unknown>

      if (type === 'condition') {
        return {
          id: safeId('wf-step'),
          type,
          definition: {
            fact: String(definition.fact ?? ''),
            operator: (definition.operator as WorkflowBuilderOperator | undefined) ?? 'eq',
            value: String(definition.value ?? '')
          }
        } satisfies WorkflowBuilderStepState
      }

      if (type === 'wait') {
        return {
          id: safeId('wf-step'),
          type,
          definition: {
            durationMinutes: String(definition.durationMinutes ?? '60')
          }
        } satisfies WorkflowBuilderStepState
      }

      if (type === 'create_client_note') {
        return {
          id: safeId('wf-step'),
          type,
          definition: {
            title: String(definition.title ?? ''),
            bodyTemplate: String(definition.bodyTemplate ?? definition.body ?? '')
          }
        } satisfies WorkflowBuilderStepState
      }

      if (type === 'send_sms') {
        return {
          id: safeId('wf-step'),
          type,
          definition: {
            bodyTemplate: String(definition.bodyTemplate ?? definition.body ?? '')
          }
        } satisfies WorkflowBuilderStepState
      }

      return {
        id: safeId('wf-step'),
        type: 'send_email',
        definition: {
          subjectTemplate: String(definition.subjectTemplate ?? definition.subject ?? ''),
          bodyTemplate: String(definition.bodyTemplate ?? definition.bodyText ?? '')
        }
      } satisfies WorkflowBuilderStepState
    })
    : [createWorkflowStep('create_client_note')]

  return {
    trigger: {
      event: String(triggerDefinition?.event ?? ''),
      subjectType: String(triggerDefinition?.subjectType ?? ''),
      filters
    },
    steps
  }
}

export function compileWorkflowBuilderToContract(state: WorkflowBuilderState) {
  return {
    triggerDefinition: {
      event: state.trigger.event.trim(),
      subjectType: state.trigger.subjectType.trim(),
      filters: state.trigger.filters
        .filter((filter) => filter.fact.trim().length > 0)
        .map((filter) => ({
          fact: filter.fact.trim(),
          operator: filter.operator,
          ...(filter.operator === 'exists' ? {} : { value: filter.value.trim() })
        }))
    },
    stepsDefinition: state.steps.map((step) => {
      if (step.type === 'condition') {
        return {
          type: step.type,
          definition: {
            fact: step.definition.fact.trim(),
            operator: step.definition.operator,
            ...(step.definition.operator === 'exists' ? {} : { value: step.definition.value.trim() })
          }
        }
      }

      if (step.type === 'wait') {
        return {
          type: step.type,
          definition: {
            durationMinutes: Number(step.definition.durationMinutes || '0')
          }
        }
      }

      if (step.type === 'create_client_note') {
        return {
          type: step.type,
          definition: {
            title: step.definition.title.trim(),
            bodyTemplate: step.definition.bodyTemplate
          }
        }
      }

      if (step.type === 'send_sms') {
        return {
          type: step.type,
          definition: {
            bodyTemplate: step.definition.bodyTemplate
          }
        }
      }

      return {
        type: step.type,
        definition: {
          subjectTemplate: step.definition.subjectTemplate,
          bodyTemplate: step.definition.bodyTemplate
        }
      }
    })
  }
}

export function validateBuilderStateBeforeSave(state: WorkflowBuilderState): string[] {
  const errors: string[] = []

  if (!state.trigger.event.trim()) {
    errors.push('Event name is required.')
  }

  if (!state.trigger.subjectType.trim()) {
    errors.push('Subject type is required.')
  }

  if (!state.steps.length) {
    errors.push('At least one workflow step is required.')
  }

  const usesClientResolutionStep = state.steps.some((step) => ['create_client_note', 'send_sms', 'send_email'].includes(step.type))
  if (
    usesClientResolutionStep
    && !WORKFLOW_RUNTIME_CLIENT_SUBJECT_TYPES.includes(state.trigger.subjectType.trim() as typeof WORKFLOW_RUNTIME_CLIENT_SUBJECT_TYPES[number])
  ) {
    errors.push('Client note, SMS, and email steps currently require a client or application subject type.')
  }

  state.steps.forEach((step, index) => {
    if (step.type === 'condition') {
      if (!step.definition.fact.trim()) errors.push(`Step ${index + 1}: condition fact is required.`)
      if (step.definition.operator !== 'exists' && !step.definition.value.trim()) {
        errors.push(`Step ${index + 1}: condition value is required.`)
      }
    }

    if (step.type === 'wait' && Number(step.definition.durationMinutes || '0') < 1) {
      errors.push(`Step ${index + 1}: wait duration must be greater than zero.`)
    }

    if (step.type === 'create_client_note' && !step.definition.bodyTemplate.trim()) {
      errors.push(`Step ${index + 1}: note body is required.`)
    }

    if (step.type === 'send_sms' && !step.definition.bodyTemplate.trim()) {
      errors.push(`Step ${index + 1}: SMS body is required.`)
    }

    if (step.type === 'send_email') {
      if (!step.definition.subjectTemplate.trim()) errors.push(`Step ${index + 1}: email subject is required.`)
      if (!step.definition.bodyTemplate.trim()) errors.push(`Step ${index + 1}: email body is required.`)
    }
  })

  return errors
}

export function summarizeWorkflowStep(step: WorkflowBuilderStepState) {
  if (step.type === 'condition') {
    return `${WORKFLOW_STEP_TYPE_LABELS[step.type]} • ${step.definition.fact || 'fact'} ${step.definition.operator} ${step.definition.value || 'value'}`
  }

  if (step.type === 'wait') {
    return `${WORKFLOW_STEP_TYPE_LABELS[step.type]} • ${step.definition.durationMinutes || '0'} minutes`
  }

  if (step.type === 'create_client_note') {
    return `${WORKFLOW_STEP_TYPE_LABELS[step.type]} • ${step.definition.title || 'Untitled note'}`
  }

  if (step.type === 'send_sms') {
    return `${WORKFLOW_STEP_TYPE_LABELS[step.type]} • ${step.definition.bodyTemplate.slice(0, 50) || 'Message body'}`
  }

  return `${WORKFLOW_STEP_TYPE_LABELS[step.type]} • ${step.definition.subjectTemplate || 'Email subject'}`
}

export const WORKFLOW_TEMPLATE_PRESETS: WorkflowTemplatePreset[] = [
  {
    key: 'blank',
    name: 'Blank starter',
    description: 'A minimal valid workflow you can immediately customize.',
    workflowKeyHint: 'client-follow-up',
    builderState: {
      trigger: { event: 'application.created', subjectType: 'application', filters: [] },
      steps: [
        {
          id: safeId('wf-step'),
          type: 'create_client_note',
          definition: {
            title: 'Follow-up',
            bodyTemplate: 'This workflow matched a newly created application.'
          }
        }
      ]
    }
  },
  {
    key: 'application-follow-up',
    name: 'Application follow-up',
    description: 'Wait briefly after an application is created, then log a follow-up note.',
    workflowKeyHint: 'application-follow-up',
    builderState: {
      trigger: { event: 'application.created', subjectType: 'application', filters: [] },
      steps: [
        {
          id: safeId('wf-step'),
          type: 'condition',
          definition: { fact: 'currentStatus', operator: 'eq', value: 'submitted' }
        },
        {
          id: safeId('wf-step'),
          type: 'wait',
          definition: { durationMinutes: '60' }
        },
        {
          id: safeId('wf-step'),
          type: 'create_client_note',
          definition: {
            title: 'Application follow-up',
            bodyTemplate: 'Follow up with this client after the submitted application entered review.'
          }
        }
      ]
    }
  },
  {
    key: 'application-sms',
    name: 'Application SMS acknowledgement',
    description: 'Send a short SMS after an application is created using a runtime-safe application subject.',
    workflowKeyHint: 'application-sms-acknowledgement',
    builderState: {
      trigger: { event: 'application.created', subjectType: 'application', filters: [] },
      steps: [
        {
          id: safeId('wf-step'),
          type: 'send_sms',
          definition: {
            bodyTemplate: 'Thanks for submitting your application. We received it and will follow up shortly.'
          }
        }
      ]
    }
  },
  {
    key: 'application-email',
    name: 'Application email acknowledgement',
    description: 'Send a short acknowledgement email after an application event.',
    workflowKeyHint: 'application-email-acknowledgement',
    builderState: {
      trigger: { event: 'application.created', subjectType: 'application', filters: [] },
      steps: [
        {
          id: safeId('wf-step'),
          type: 'send_email',
          definition: {
            subjectTemplate: 'We received your application',
            bodyTemplate: 'Thank you. Your application has been received and is now in our review workflow.'
          }
        }
      ]
    }
  }
]

export function cloneBuilderState(state: WorkflowBuilderState): WorkflowBuilderState {
  return parseWorkflowContractToBuilderState(
    compileWorkflowBuilderToContract(state).triggerDefinition,
    compileWorkflowBuilderToContract(state).stepsDefinition as Array<Record<string, unknown>>
  )
}

export function getWorkflowTemplatePreset(key: string) {
  return WORKFLOW_TEMPLATE_PRESETS.find((preset) => preset.key === key) ?? WORKFLOW_TEMPLATE_PRESETS[0]
}
