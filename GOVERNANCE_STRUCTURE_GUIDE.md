# Governance Structure System Guide

## Overview
The Governance Structure system provides a comprehensive organizational hierarchy management solution for the ATTP application. It enables you to define, manage, and track organizational relationships with temporal validity.

---

## Features

### Core Capabilities
- **Hierarchical Levels**: Define multiple levels (Organ → Commission → Department → Directorate → Division/Unit)
- **Organizational Nodes**: Create and manage individual organizational units
- **Reporting Lines**: Establish three types of relationships:
  - **Primary**: Formal hierarchy (one active primary per node)
  - **Dotted**: Matrix reporting for cross-functional work
  - **Advisory**: Guidance relationship without line authority
- **Node Assignments**: Assign employees to nodes with roles and effective dates
- **Temporal Tracking**: All relationships support effective start and end dates
- **Data Export**: All tables support Excel, PDF, CSV, Copy, and Print exports

---

## Database Structure

### Tables Created

#### 1. `myb_governance_levels`
Defines the hierarchy levels in your organization.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| key | VARCHAR(50) | Unique identifier key (e.g., 'organ', 'commission') |
| name | VARCHAR(100) | Display name (e.g., 'Organ', 'Commission') |
| sort_order | INTEGER | Display order in hierarchy (lower = higher level) |
| description | TEXT | Description of this level |
| created_at | TIMESTAMP | Record creation timestamp |
| updated_at | TIMESTAMP | Record update timestamp |

**Indexes:**
- Primary: `id`
- Unique: `key`
- Index: `sort_order`, `name`

**Example Data:**
```sql
INSERT INTO myb_governance_levels (key, name, sort_order, description) VALUES
('organ', 'Organ', 1, 'Top-level organizational unit'),
('commission', 'Commission', 2, 'Major organizational division'),
('department', 'Department', 3, 'Departmental level'),
('directorate', 'Directorate', 4, 'Directorate level'),
('division', 'Division/Unit', 5, 'Smallest organizational unit');
```

#### 2. `myb_governance_nodes`
Individual organizational units/entities.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| level_id | BIGINT | FK to myb_governance_levels |
| name | VARCHAR(200) | Name of the organizational unit |
| code | VARCHAR(50) | Optional unique code/identifier |
| description | TEXT | Description |
| status | ENUM | Status: active, inactive, pending, archived |
| effective_start | DATE | When this node becomes effective |
| effective_end | DATE | When this node becomes inactive |
| created_by | BIGINT | FK to users (who created) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- Primary: `id`
- Foreign Keys: `level_id`, `created_by`
- Unique: `code`
- Composite: `(level_id, status)`, `(status, effective_start)`, `(level_id, status, effective_start)`
- Single: `name`

**Constraints:**
- `level_id` RESTRICT on delete (prevent deletion if nodes exist)
- Soft deletes enabled for data integrity

#### 3. `myb_governance_reporting_lines`
Defines relationships between nodes.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| child_node_id | BIGINT | FK to myb_governance_nodes (subordinate) |
| parent_node_id | BIGINT | FK to myb_governance_nodes (superior) |
| line_type | ENUM | Type: primary, dotted, advisory |
| effective_start | DATE | When this line becomes active |
| effective_end | DATE | When this line ends |
| created_by | BIGINT | FK to users (who created) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- Primary: `id`
- Foreign Keys: `child_node_id`, `parent_node_id`, `created_by`
- Unique: `(child_node_id, parent_node_id, line_type, effective_start)`
- Composite: `(child_node_id, line_type)`, `(parent_node_id, line_type)`, `(line_type, effective_start)`, `(child_node_id, effective_start, effective_end)`

**Constraints:**
- CASCADE on delete for both child and parent nodes
- Soft deletes for historical tracking
- Unique constraint prevents duplicate relationships

**Business Rules:**
- Only ONE active primary reporting line per node at any given time
- Multiple dotted and advisory lines are allowed
- Validated in controller logic

#### 4. `myb_governance_node_assignments`
Assigns users/employees to nodes with roles.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| node_id | BIGINT | FK to myb_governance_nodes |
| user_id | BIGINT | FK to users (assigned employee) |
| role_title | VARCHAR(150) | Job title or role in this node |
| is_primary | BOOLEAN | Is this the primary assignment? |
| effective_start | DATE | Assignment start date |
| effective_end | DATE | Assignment end date |
| created_by | BIGINT | FK to users (who created) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Update timestamp |
| deleted_at | TIMESTAMP | Soft delete timestamp |

**Indexes:**
- Primary: `id`
- Foreign Keys: `node_id`, `user_id`, `created_by`
- Composite: `(user_id, node_id, effective_start)`, `(node_id, is_primary)`, `(user_id, is_primary)`, `(node_id, effective_start, effective_end)`, `(user_id, effective_start, effective_end)`
- Single: `role_title`

**Constraints:**
- CASCADE on delete for node and user
- Soft deletes for historical tracking
- Composite index prevents duplicate assignments

**Business Rules:**
- Only ONE active primary assignment per user at any given time
- Multiple non-primary assignments are allowed
- Validated in controller logic

---

## User Interface

### Navigation
Access via: **Finance** → **Governance Structure**

The interface has 3 tabs:
1. **Structure Nodes** - Manage organizational units
2. **Reporting Lines** - Define relationships between nodes
3. **Assignments** - Assign employees to nodes

### Structure Nodes Tab

#### Features:
- **Add Node Form**: Create new organizational nodes
- **DataTable**: View, search, filter, and export all nodes
- **Inline Edit**: Click "Edit" to modify nodes inline
- **Delete**: Remove nodes (with cascade protection)
- **Export**: Excel, PDF, CSV, Print options

#### Add Node:
1. Select Level (Organ, Commission, Department, etc.)
2. Enter Name (required)
3. Enter Code (optional, must be unique)
4. Set Effective Start date (optional)
5. Add Description (optional)
6. Select Status (Active/Inactive)

#### Table Columns:
- **Level**: Badge showing the hierarchy level
- **Name**: Node name with description
- **Code**: Unique identifier
- **Status**: Active/Inactive badge
- **Effective Start**: When node becomes effective
- **Actions**: Edit/Delete buttons (excluded from exports)

### Reporting Lines Tab

#### Features:
- **Add Line Form**: Create reporting relationships
- **DataTable**: View, search, and export all lines
- **Type Badges**: Color-coded line types (Primary=Green, Dotted=Blue, Advisory=Yellow)
- **Inline Edit**: Modify relationships inline

#### Add Reporting Line:
1. Select Child Node (subordinate)
2. Select Parent Node (superior)
3. Select Line Type:
   - **Primary**: Formal hierarchy
   - **Dotted**: Matrix reporting
   - **Advisory**: Guidance only
4. Set Effective Start (optional)
5. Set Effective End (optional, leave blank for open-ended)

#### Table Columns:
- **Child Node**: Subordinate unit with level
- **Parent Node**: Superior unit with level
- **Type**: Badge showing line type
- **Effective**: Date range (start → end or "Open")
- **Actions**: Edit/Delete buttons

### Assignments Tab

#### Features:
- **Add Assignment Form**: Assign employees to nodes
- **Employee Search**: Filter dropdown by name/email
- **Email Notifications**: Optional notification to assigned user
- **DataTable**: View, search, and export all assignments

#### Add Assignment:
1. Select Node (organizational unit)
2. Search and select Employee (user in system)
3. Enter Role Title (optional, e.g., "Director", "Manager")
4. Set Effective Start/End dates (optional)
5. Check "Primary Assignment" if this is the user's main role
6. Check "Email notification" to notify the user

#### Table Columns:
- **Node**: Organizational unit with level
- **Employee**: User name and email
- **Role**: Badge showing role title
- **Primary**: Yes/No badge
- **Effective**: Date range
- **Actions**: Edit/Delete buttons

---

## Permissions

### Required Permissions:
- `finance.governance_structure.view` - View governance structure
- `finance.governance_structure.create` - Create nodes/lines/assignments
- `finance.governance_structure.edit` - Edit governance items
- `finance.governance_structure.delete` - Delete governance items
- `finance.governance_structure.manage` - Full management (all above)

---

## Export Functionality

All three tables support full export:
- **Copy**: Copy table data to clipboard
- **CSV**: Export as comma-separated values
- **Excel**: Export as .xlsx file
- **PDF**: Export as PDF (landscape, A4, with styling)
- **Print**: Print-friendly view
- **Columns**: Toggle column visibility

**Note**: Actions column is automatically excluded from all exports.

---

## Database Optimization

### Performance Enhancements:
1. **Composite Indexes**: Optimized for common query patterns
2. **Soft Deletes**: Historical tracking without data loss
3. **Foreign Key Constraints**: Data integrity at database level
4. **Enum Types**: Line types and statuses for data validation
5. **Unique Constraints**: Prevent duplicate relationships
6. **Index on Names**: Fast search queries

### Query Optimization Examples:

**Get active nodes by level:**
```sql
SELECT * FROM myb_governance_nodes
WHERE level_id = ? AND status = 'active' AND effective_start <= CURDATE()
ORDER BY name;
-- Uses index: (level_id, status)
```

**Get reporting hierarchy for a node:**
```sql
SELECT * FROM myb_governance_reporting_lines
WHERE child_node_id = ? AND line_type = 'primary'
AND effective_start <= CURDATE() AND (effective_end IS NULL OR effective_end >= CURDATE());
-- Uses index: (child_node_id, line_type)
```

**Get user's current assignments:**
```sql
SELECT * FROM myb_governance_node_assignments
WHERE user_id = ? AND effective_start <= CURDATE()
AND (effective_end IS NULL OR effective_end >= CURDATE())
ORDER BY is_primary DESC;
-- Uses index: (user_id, effective_start, effective_end)
```

---

## Validation Rules

### Controller Validations:

#### Structure Nodes:
```php
'level_id' => 'required|exists:myb_governance_levels,id',
'name' => 'required|string|max:200',
'code' => 'nullable|string|max:50|unique:myb_governance_nodes,code',
'status' => 'required|in:active,inactive',
'effective_start' => 'nullable|date',
'description' => 'nullable|string'
```

#### Reporting Lines:
```php
'child_node_id' => 'required|exists:myb_governance_nodes,id',
'parent_node_id' => 'required|exists:myb_governance_nodes,id|different:child_node_id',
'line_type' => 'required|in:primary,dotted,advisory',
'effective_start' => 'nullable|date',
'effective_end' => 'nullable|date|after_or_equal:effective_start'
```

**Business Logic Validation:**
- Prevent node from reporting to itself
- Ensure only one active primary line per node during date range
- Prevent circular reporting relationships

#### Assignments:
```php
'node_id' => 'required|exists:myb_governance_nodes,id',
'user_id' => 'required|exists:users,id',
'role_title' => 'nullable|string|max:150',
'is_primary' => 'boolean',
'effective_start' => 'nullable|date',
'effective_end' => 'nullable|date|after_or_equal:effective_start',
'notify_user' => 'boolean'
```

**Business Logic Validation:**
- Ensure only one active primary assignment per user during date range

---

## Usage Examples

### Example 1: Building Organization Hierarchy

**Step 1: Create Governance Levels** (if not seeded)
1. Organ (sort_order: 1)
2. Commission (sort_order: 2)
3. Department (sort_order: 3)

**Step 2: Create Nodes**
1. Create Organ: "African Union"
2. Create Commission: "AUC" under Organ level
3. Create Department: "Peace and Security" under Department level

**Step 3: Create Reporting Lines**
1. AUC (child) → African Union (parent), Type: Primary
2. Peace and Security (child) → AUC (parent), Type: Primary

**Step 4: Assign Employees**
1. Assign Commissioner to AUC, Primary: Yes, Role: "Commissioner"
2. Assign Director to Peace and Security, Primary: Yes, Role: "Director"

### Example 2: Matrix Organization

For cross-functional teams:
1. Create primary line: Team Member → Department Head (Primary)
2. Create dotted line: Team Member → Project Manager (Dotted)
3. Both relationships can exist simultaneously with different effective dates

---

## Integration with Other Modules

The `governance_node_id` column has been added to:
- **users** - User's primary organizational node
- **sectors** - Sector ownership
- **programs** - Program ownership
- **projects** - Project ownership
- **activities** - Activity ownership
- **sub_activities** - Sub-activity ownership
- **program_fundings** - Funding responsibility
- **budget_commitments** - Budget responsibility
- **resource_categories** - Resource ownership
- **resources** - Resource ownership
- **procurements** - Procurement responsibility

This enables scope-based access control and organizational filtering.

---

## Best Practices

### 1. Effective Dating
- Always set effective_start for new relationships
- Use effective_end when relationships expire
- Leave effective_end NULL for open-ended relationships
- Query with date filters to get current state

### 2. Primary Relationships
- Each node should have ONE primary reporting line
- Each user should have ONE primary assignment
- System validates this during creation/update

### 3. Status Management
- Use 'active' for current nodes
- Use 'inactive' for temporarily disabled nodes
- Use 'archived' for historical nodes
- Use 'pending' for nodes not yet active

### 4. Code Management
- Use consistent naming conventions (e.g., "ORG-001", "COM-001")
- Codes must be unique across all nodes
- Optional but recommended for external integrations

### 5. Role Titles
- Be consistent with role naming
- Use standard titles where possible
- Include role titles in assignments for clarity

---

## Troubleshooting

### Common Issues:

**"Cannot delete node" Error**
- Check if node has active reporting lines
- Check if node has active assignments
- Migration uses RESTRICT on level deletion

**"Duplicate primary line" Error**
- Only one primary line allowed per node per period
- Check effective dates for overlap
- Set effective_end on old line before creating new one

**"User not found in dropdown"**
- Use the search box to filter users
- Ensure user exists in users table
- Check if user account is active

**Export not working**
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify DataTables libraries are loaded

---

## Summary

The Governance Structure system provides:
- ✅ Complete organizational hierarchy management
- ✅ Three types of reporting relationships
- ✅ Temporal validity tracking
- ✅ Employee assignment management
- ✅ Full data export capabilities
- ✅ Optimized database structure
- ✅ Beautiful, modern UI with DataTables
- ✅ Integration with all major modules

**Key Files:**
- View: `resources/views/finance/governance/index.blade.php`
- Controller: `app/Http/Controllers/GovernanceStructureController.php`
- Models: `app/Models/GovernanceLevel.php`, `GovernanceNode.php`, `GovernanceReportingLine.php`, `GovernanceNodeAssignment.php`
- Migration: `database/migrations/2026_01_27_120000_create_governance_structure_tables.php`
- Routes: `routes/web.php` (lines 526-567)

---

**Last Updated:** 2026-01-29
**Version:** 2.0 - Enhanced with DataTables and optimized database
