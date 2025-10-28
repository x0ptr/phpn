<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Task Manager - Laravel Desktop Application

A fully-featured task management application built with Laravel, demonstrating how well Laravel works as a native macOS desktop application with WebKit integration via PHPN.

## Features

### 📋 Project Management
- Create, edit, and delete projects
- Color-coded projects for easy identification
- Progress tracking with visual indicators
- Drag-and-drop project reordering

### ✅ Task Management
- Create tasks with priorities (low, medium, high, urgent)
- Set due dates and track overdue tasks
- Task descriptions with markdown support
- Drag-and-drop tasks between status columns (Kanban board)
- Quick complete/incomplete toggle
- Task filtering by status

### 🏷️ Tags & Organization
- Create custom tags with colors
- Tag tasks for better organization
- Tag filtering and management

### 📊 Dashboard
- Overview statistics (total, completed, in progress, overdue)
- Due today section
- Overdue tasks alerts
- Recent tasks activity
- Project progress cards

### 🎨 Modern UI
- Clean, responsive design with Tailwind CSS
- Drag-and-drop interactions
- Real-time updates via AJAX
- Smooth animations and transitions
- Mobile-friendly layout

## Technical Highlights

This application showcases Laravel's capabilities in a desktop environment:

- **Eloquent ORM**: Rich relationships between Projects, Tasks, and Tags
- **Database Migrations**: Structured schema with proper indexes
- **Blade Templates**: Reusable components and layouts
- **Form Validation**: Server-side validation for all inputs
- **Query Scopes**: Reusable query filters (overdue, completed, etc.)
- **Model Events**: Automatic position management
- **Accessors/Mutators**: Computed attributes (progress percentage, overdue status)
- **AJAX Integration**: Seamless updates without page reloads
- **SQLite Database**: Local data storage perfect for desktop apps

## Database Schema

```
projects
├── id
├── name
├── description
├── color
├── position
└── timestamps

tasks
├── id
├── project_id (FK)
├── title
├── description
├── priority (enum)
├── status (enum)
├── due_date
├── position
├── completed_at
└── timestamps

tags
├── id
├── name
├── color
└── timestamps

task_tag (pivot)
├── task_id (FK)
└── tag_id (FK)
```

## Setup Instructions

1. **Install Dependencies**
   ```bash
   cd examples/laravel-app
   composer install
   ```

2. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   - The app uses SQLite by default
   - Database file: `database/database.sqlite`
   ```bash
   touch database/database.sqlite
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Seed Sample Data**
   ```bash
   php artisan db:seed
   ```
   This creates:
   - 3 sample projects (Website Redesign, Mobile App, Marketing Campaign)
   - 15+ sample tasks with various statuses and priorities
   - 6 predefined tags

6. **Run with PHPN**
   ```bash
   ../../bin/phpn serve public/index.php
   ```

## Sample Data

The seeder creates realistic sample data:

**Projects:**
- 🌐 Website Redesign (Blue) - Web development tasks
- 📱 Mobile App Development (Green) - Mobile app tasks  
- 📢 Q1 Marketing Campaign (Orange) - Marketing activities

**Tags:**
- 🐛 Bug (Red)
- ✨ Feature (Blue)
- 📝 Documentation (Purple)
- 🎨 Design (Pink)
- 🧪 Testing (Green)
- ⚠️ Urgent (Orange)

## Usage Examples

### Creating a Project
1. Click "+ New Project" from Dashboard or Projects page
2. Enter name, description, and choose a color
3. Start adding tasks!

### Managing Tasks
1. Navigate to a project
2. Add tasks using the form at the top
3. Drag tasks between columns (To Do → In Progress → Completed)
4. Click checkbox for quick complete/incomplete
5. Click X to delete a task

### Using Tags
1. Go to Tags page
2. Create tags with custom colors
3. Assign tags when creating/editing tasks
4. Use tags to filter and organize tasks

## Development Notes

### Key Files
- **Models**: `app/Models/{Project,Task,Tag}.php`
- **Controllers**: `app/Http/Controllers/{Dashboard,Project,Task,Tag}Controller.php`
- **Views**: `resources/views/{dashboard,projects,tasks,tags}/*.blade.php`
- **Routes**: `routes/web.php`
- **Migrations**: `database/migrations/2024_01_01_*.php`
- **Seeder**: `database/seeders/DatabaseSeeder.php`

### Eloquent Relationships
```php
// Project has many Tasks
$project->tasks

// Task belongs to Project
$task->project

// Task has many Tags (many-to-many)
$task->tags

// Tag has many Tasks (many-to-many)
$tag->tasks
```

### Query Examples
```php
// Get overdue tasks
Task::overdue()->get()

// Get high priority tasks
Task::highPriority()->get()

// Get project with task counts
Project::withCount(['tasks', 'completedTasks'])->get()

// Tasks due today
Task::whereDate('due_date', today())->get()
```

## Future Enhancements

Potential features to add:
- [ ] Task attachments/files
- [ ] Task comments/notes
- [ ] Subtasks/checklists
- [ ] Search functionality
- [ ] Export to PDF/CSV
- [ ] Recurring tasks
- [ ] Time tracking
- [ ] Team collaboration
- [ ] Notifications
- [ ] Dark mode

## Why Laravel + PHPN?

This application demonstrates that Laravel is an excellent choice for desktop applications:

✅ **Familiar Tools**: Use Laravel's ecosystem you already know
✅ **Rapid Development**: Build features quickly with Eloquent, Blade, etc.
✅ **Rich Features**: Access to all Laravel packages and libraries
✅ **Local-First**: SQLite for offline-capable apps
✅ **Native Feel**: WebKit integration for responsive UI
✅ **Cross-Platform Potential**: Same codebase could run on multiple OS

---

Built with ❤️ using Laravel and PHPN Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
