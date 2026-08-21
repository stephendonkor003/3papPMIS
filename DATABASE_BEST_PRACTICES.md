# Database Best Practices for ATTP Application

## Overview
This guide ensures all database tables in the ATTP application follow Laravel best practices for optimal performance, maintainability, and scalability.

---

## Table Design Principles

### 1. Naming Conventions

#### Table Names
- Use **plural**, **snake_case** for table names
- Examples: `users`, `project_categories`, `budget_allocations`

```php
Schema::create('budget_allocations', function (Blueprint $table) {
    // ...
});
```

#### Column Names
- Use **snake_case** for column names
- Be descriptive but concise
- Examples: `first_name`, `created_at`, `user_id`

```php
$table->string('first_name');
$table->foreignId('user_id');
$table->timestamp('approved_at')->nullable();
```

#### Foreign Keys
- Format: `{singular_table_name}_id`
- Examples: `user_id`, `category_id`, `project_id`

```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
```

---

### 2. Primary Keys

Always use auto-incrementing IDs:

```php
Schema::create('items', function (Blueprint $table) {
    $table->id(); // Auto-incrementing BIGINT UNSIGNED
    // ... other columns
});
```

For composite keys (rare cases):

```php
$table->primary(['user_id', 'role_id']);
```

---

### 3. Timestamps

**Always** include timestamps:

```php
Schema::create('items', function (Blueprint $table) {
    $table->id();
    // ... columns
    $table->timestamps(); // created_at, updated_at
});
```

For soft deletes:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

// In migration
$table->softDeletes(); // deleted_at

// In model
class Item extends Model
{
    use SoftDeletes;
}
```

---

### 4. Indexes

#### Single Column Indexes

Index columns that are:
- Frequently searched
- Used in WHERE clauses
- Used in ORDER BY
- Foreign keys (automatically indexed)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique(); // Unique index
    $table->string('name')->index();   // Regular index
    $table->string('phone')->nullable()->index();
    $table->enum('status', ['active', 'inactive', 'pending'])->default('pending')->index();
    $table->timestamps();
});
```

#### Composite Indexes

For queries filtering on multiple columns:

```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('user_id')->constrained();
    $table->enum('status', ['pending', 'active', 'completed']);
    $table->timestamps();

    // Composite index for common query: WHERE user_id = ? AND status = ?
    $table->index(['user_id', 'status']);

    // Composite index for date range queries
    $table->index(['status', 'created_at']);
});
```

#### Full-Text Indexes

For text search:

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamps();

    // Full-text index
    $table->fullText(['title', 'content']);
});

// Usage in query
Article::whereFullText(['title', 'content'], 'search term')->get();
```

---

### 5. Data Types

Choose appropriate data types:

```php
Schema::create('users', function (Blueprint $table) {
    // Strings
    $table->string('name', 100);           // VARCHAR(100)
    $table->string('email')->unique();     // VARCHAR(255) - default
    $table->text('bio')->nullable();       // TEXT
    $table->longText('content')->nullable(); // LONGTEXT

    // Numbers
    $table->integer('age');                // INT
    $table->bigInteger('large_number');    // BIGINT
    $table->tinyInteger('status');         // TINYINT (0-255)
    $table->decimal('price', 10, 2);       // DECIMAL(10,2) - for money
    $table->float('rating', 3, 2);         // FLOAT(3,2)

    // Boolean
    $table->boolean('is_active')->default(true);

    // Dates & Times
    $table->date('birth_date');
    $table->time('meeting_time');
    $table->dateTime('appointment_at');
    $table->timestamp('verified_at')->nullable();

    // JSON
    $table->json('metadata')->nullable();

    // Enums
    $table->enum('role', ['admin', 'user', 'manager'])->default('user');

    $table->timestamps();
});
```

---

### 6. Constraints

#### NOT NULL vs NULLABLE

```php
// Required fields (NOT NULL by default)
$table->string('name');
$table->string('email');

// Optional fields
$table->string('phone')->nullable();
$table->text('notes')->nullable();
$table->timestamp('approved_at')->nullable();
```

#### Default Values

```php
$table->boolean('is_active')->default(true);
$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
$table->integer('views')->default(0);
$table->timestamp('created_at')->useCurrent();
```

#### Unique Constraints

```php
// Single column
$table->string('email')->unique();

// Multiple columns
$table->unique(['user_id', 'role_id']);

// Named unique constraint
$table->unique(['email'], 'users_email_unique');
```

---

### 7. Foreign Key Constraints

Always use foreign key constraints:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');

    // Foreign key with cascade delete
    $table->foreignId('user_id')
          ->constrained('users')
          ->onDelete('cascade')
          ->onUpdate('cascade');

    // Foreign key with restrict (prevent deletion)
    $table->foreignId('category_id')
          ->constrained('categories')
          ->onDelete('restrict');

    // Foreign key with set null
    $table->foreignId('approved_by')
          ->nullable()
          ->constrained('users')
          ->onDelete('set null');

    $table->timestamps();
});
```

#### Foreign Key Actions

| Action | Behavior |
|--------|----------|
| `cascade` | Delete/update child records when parent is deleted/updated |
| `restrict` | Prevent deletion if child records exist |
| `set null` | Set foreign key to NULL when parent is deleted |
| `no action` | Database default behavior |

---

### 8. Pivot Tables (Many-to-Many)

```php
// Table name: singular_singular in alphabetical order
Schema::create('project_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');

    // Additional pivot data
    $table->enum('role', ['owner', 'member', 'viewer'])->default('member');
    $table->timestamp('joined_at')->useCurrent();

    $table->timestamps();

    // Prevent duplicate entries
    $table->unique(['project_id', 'user_id']);
});
```

---

### 9. Polymorphic Relationships

```php
// For comments that can belong to posts, videos, etc.
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->morphs('commentable'); // Creates commentable_id and commentable_type

    // Or manually:
    // $table->unsignedBigInteger('commentable_id');
    // $table->string('commentable_type');

    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    // Index for polymorphic queries
    $table->index(['commentable_id', 'commentable_type']);
});
```

---

## Performance Optimization

### 1. Eager Loading (Prevent N+1 Queries)

**Bad:**
```php
$projects = Project::all();
foreach ($projects as $project) {
    echo $project->user->name; // N+1 query problem!
}
```

**Good:**
```php
$projects = Project::with('user')->get();
foreach ($projects as $project) {
    echo $project->user->name; // Single query!
}
```

**Complex Eager Loading:**
```php
$projects = Project::with([
    'user',
    'category',
    'activities' => function ($query) {
        $query->where('status', 'active')->orderBy('created_at', 'desc');
    },
    'activities.allocations'
])->get();
```

---

### 2. Query Optimization

#### Use Select to Limit Columns

**Bad:**
```php
$users = User::all(); // Fetches all columns
```

**Good:**
```php
$users = User::select('id', 'name', 'email')->get();
```

#### Use Chunking for Large Datasets

```php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Or use lazy loading
User::lazy()->each(function ($user) {
    // Process user
});
```

#### Use Raw Queries for Complex Operations

```php
use Illuminate\Support\Facades\DB;

$results = DB::select('
    SELECT p.*, COUNT(a.id) as activity_count
    FROM projects p
    LEFT JOIN activities a ON p.id = a.project_id
    GROUP BY p.id
    HAVING activity_count > 5
');
```

---

### 3. Database Indexing Best Practices

#### Check Index Usage

```bash
# Run this SQL to check index usage
SELECT
    TABLE_NAME,
    INDEX_NAME,
    SEQ_IN_INDEX,
    COLUMN_NAME
FROM
    information_schema.STATISTICS
WHERE
    TABLE_SCHEMA = 'your_database_name'
ORDER BY
    TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

#### Don't Over-Index

**Problems with too many indexes:**
- Slower INSERT/UPDATE/DELETE operations
- More storage space
- Increased maintenance overhead

**Guidelines:**
- Index columns used in WHERE clauses
- Index foreign keys (Laravel does this automatically)
- Index columns used in ORDER BY
- Don't index columns with low cardinality (e.g., boolean fields)
- Don't index columns that are rarely queried

---

## Migration Best Practices

### 1. Migration File Organization

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->decimal('budget', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
```

### 2. Migration Order

Ensure migrations run in the correct order:

1. Create independent tables first (e.g., `users`, `categories`)
2. Create dependent tables later (e.g., `projects` that reference `categories`)
3. Use timestamps in migration filenames to control order

### 3. Seeding

Create seeders for test data:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'description' => 'Tech projects'],
            ['name' => 'Education', 'description' => 'Educational projects'],
            ['name' => 'Health', 'description' => 'Health projects'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
```

---

## Model Best Practices

### 1. Mass Assignment Protection

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    // Option 1: Whitelist fillable fields
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'budget',
        'status'
    ];

    // Option 2: Blacklist guarded fields
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Option 3: Allow all (NOT recommended for production)
    // protected $guarded = [];
}
```

### 2. Type Casting

```php
class Project extends Model
{
    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array', // JSON field
        'verified_at' => 'datetime',
    ];
}
```

### 3. Relationships

```php
class Project extends Model
{
    // One-to-Many (Inverse)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // One-to-Many
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // Many-to-Many
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Polymorphic
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

---

## Database Maintenance Commands

### Run Migrations

```bash
# Run all pending migrations
php artisan migrate

# Run migrations with seeding
php artisan migrate --seed

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Drop all tables and re-run migrations
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Database Inspection

```bash
# List all tables
php artisan db:table

# Show table structure
php artisan db:show {table_name}

# Monitor database queries
php artisan db:monitor
```

---

## Security Best Practices

### 1. SQL Injection Prevention

**Bad:**
```php
$users = DB::select("SELECT * FROM users WHERE email = '$email'"); // VULNERABLE!
```

**Good:**
```php
$users = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
// Or use Query Builder
$users = User::where('email', $email)->get();
```

### 2. Mass Assignment Protection

Always use `$fillable` or `$guarded` in models.

### 3. Encryption for Sensitive Data

```php
use Illuminate\Database\Eloquent\Casts\Encrypted;

class User extends Model
{
    protected $casts = [
        'secret_key' => Encrypted::class,
    ];
}
```

---

## Database Health Check

Create a custom Artisan command to check database health:

```bash
php artisan db:health-check
```

This will verify:
- ✅ All foreign keys are valid
- ✅ No orphaned records
- ✅ Proper indexes on foreign keys
- ✅ No missing indexes on commonly queried columns
- ✅ Table sizes and row counts

---

## Summary Checklist

When creating a new table, ensure:

- [ ] Table name is plural, snake_case
- [ ] Primary key is `id()` (auto-incrementing)
- [ ] All foreign keys have constraints
- [ ] Appropriate indexes on searchable columns
- [ ] Timestamps (`created_at`, `updated_at`)
- [ ] Soft deletes if needed (`deleted_at`)
- [ ] Proper data types for columns
- [ ] Default values where appropriate
- [ ] NOT NULL constraints on required fields
- [ ] Unique constraints where needed
- [ ] Model has `$fillable` or `$guarded`
- [ ] Model has proper type casting
- [ ] Relationships defined in model
- [ ] Migration has `down()` method

---

## Additional Resources

- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Laravel Eloquent Documentation](https://laravel.com/docs/eloquent)
- [MySQL Index Best Practices](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)

---

**Last Updated:** 2026-01-29
