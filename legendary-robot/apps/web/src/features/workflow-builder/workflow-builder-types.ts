export type WorkflowBuilderOperator = 'eq' | 'neq' | 'gte' | 'lte' | 'contains' | 'exists'
export type WorkflowBuilderStepType = 'condition' | 'wait' | 'create_client_note' | 'send_sms' | 'send_email'

export type WorkflowTriggerFilter = {
  id: string
  fact: string
  operator: WorkflowBuilderOperator
  value: string
}

export type WorkflowTriggerBuilderState = {
  event: string
  subjectType: string
  filters: WorkflowTriggerFilter[]
}

export type WorkflowConditionStepState = {
  fact: string
  operator: WorkflowBuilderOperator
  value: string
}

export type WorkflowWaitStepState = {
  durationMinutes: string
}

export type WorkflowClientNoteStepState = {
  title: string
  bodyTemplate: string
}

export type WorkflowSmsStepState = {
  bodyTemplate: string
}

export type WorkflowEmailStepState = {
  subjectTemplate: string
  bodyTemplate: string
}

export type WorkflowBuilderStepState = {
  id: string
  type: WorkflowBuilderStepType
  definition:
    | WorkflowConditionStepState
    | WorkflowWaitStepState
    | WorkflowClientNoteStepState
    | WorkflowSmsStepState
    | WorkflowEmailStepState
}

export type WorkflowBuilderState = {
  trigger: WorkflowTriggerBuilderState
  steps: WorkflowBuilderStepState[]
}

export type WorkflowTemplatePreset = {
  key: string
  name: string
  description: string
  workflowKeyHint: string
  builderState: WorkflowBuilderState
}
