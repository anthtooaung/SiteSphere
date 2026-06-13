---
name: knowledges-management
description: Manage project knowledge files in the 'knowledges' folder. Use when updating documentation, tracking task status, or recording findings as mandated by project rules (e.g., when token usage is high).
---

# Knowledges Management

## Overview

This skill guides the management of the `knowledges/` directory, which serves as the project's living documentation and task registry. It ensures consistency across findings, status updates, and "remain tasks" recording.

## Core Knowledge Files

- **remainTask.md**: The primary registry for pending tasks. ALWAYS update this when stopping a task due to high token usage (20%+).
- **testingTask.md**: Tracks testing progress and sidebar layout verification.
- **backend-logic-status.md**: Documents the state of backend implementation.
- **dashboard-design-check.md**: Audit of dashboard UI/UX consistency.
- **project-design-repairing.md**: History of UI/UX fixes and improvements.

## Workflows

### 1. Recording "Remain Tasks" (Token Limit)
When token usage reaches 20%, you MUST:
1. Identify the current state of the task.
2. List all incomplete sub-tasks and blockers.
3. Append these to `knowledges/remainTask.md` under a clear heading.
4. Include any context or partial work that will help resume the task.

### 2. Updating Findings
After investigating a feature or bug:
1. Identify the relevant knowledge file (e.g., `font-flexibility.md` for font issues).
2. Update the file with new findings, technical debt identified, or resolved items.
3. If no relevant file exists, consider creating a new one if the finding is significant.

### 3. Verifying Design Consistency
When checking UI components:
1. Refer to `knowledges/dashboard-design-check.md` for established patterns.
2. Update this file if new inconsistencies are found or if a component's design is refined.

## Guidelines

- **Conciseness**: Keep updates brief and technical.
- **Accuracy**: Ensure file paths and symbol names match the current codebase.
- **Traceability**: Link findings to specific files or commits if possible.
