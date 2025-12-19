# Dropout Prediction System - Project Structure

## Project Overview

A Laravel-based web application with Python analytics for predicting student dropout rates.

```
dropout-prediction/
├── .DS_Store
├── .editorconfig
├── .env
├── .env.example
├── .prettierignore
├── .prettierrc
├── artisan
├── components.json
├── composer.json
├── composer.lock
├── eslint.config.js
├── package-lock.json
├── package.json
├── phpunit.xml
│
├── app/                                    # Laravel Application Code
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── .DS_Store
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ExternalDataController.php
│   │   │   ├── AuthController.php
│   │   │   ├── Api/
│   │   │   │   ├── .DS_Store
│   │   │   │   ├── CollegeAdminController.php
│   │   │   │   └── StudentController.php
│   │   │   ├── Auth/                       # Authentication Controllers
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   └── Settings/                   # Settings Controllers
│   │   │       ├── PasswordController.php
│   │   │       └── ProfileController.php
│   │   ├── Middleware/
│   │   │   ├── HandleAppearance.php
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   └── LoginRequest.php
│   │       └── Settings/
│   │           └── ProfileUpdateRequest.php
│   ├── Models/                             # Database Models
│   │   ├── User.php
│   │   ├── CollegeAdmin.php
│   │   ├── Student.php
│   │   ├── Prediction.php
│   │   └── ExternalDataset.php
│   └── Providers/
│       └── AppServiceProvider.php
│
├── bootstrap/                              # Laravel Bootstrap
│   ├── app.php
│   ├── providers.php
│   └── cache/
│       ├── .gitignore
│       ├── packages.php
│       └── services.php
│
├── config/                                 # Configuration Files
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── inertia.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
│
├── database/                               # Database Files
│   ├── .gitignore
│   ├── database.sqlite                     # SQLite Database
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/                         # Database Migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_07_09_082113_create_college_admins_table.php
│   │   ├── 2025_07_09_082131_create_students_table.php
│   │   ├── 2025_07_09_082152_create_predictions_table.php
│   │   └── 2025_07_09_082209_create_external_datasets_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/                                 # Public Assets
│   ├── .htaccess
│   ├── apple-touch-icon.png
│   ├── favicon.ico
│   ├── favicon.svg
│   ├── index.php
│   ├── robots.txt
│   └── build/                              # Compiled Assets
│       ├── manifest.json
│       └── assets/                         # JS/CSS Build Files
│           ├── AppLayout.vue_vue_type_script_setup_true_lang-DZHRK5an.js
│           ├── AppLogoIcon.vue_vue_type_script_setup_true_lang-T4xjoQ-P.js
│           ├── Appearance-jjHkKmAD.js
│           ├── AuthLayout.vue_vue_type_script_setup_true_lang-XEq0cyaN.js
│           ├── ConfirmPassword-C5_Jhg94.js
│           ├── Dashboard-O_f1LgBp.js
│           ├── ForgotPassword-YivZ8dc3.js
│           ├── Label.vue_vue_type_script_setup_true_lang-Dj3uiGa-.js
│           ├── Layout.vue_vue_type_script_setup_true_lang-Bku_QMKm.js
│           ├── Login-B9_Po0kE.js
│           ├── Password-CcCxbaiA.js
│           ├── Profile-BZEUxPzd.js
│           ├── Register-B9o-l43L.js
│           ├── ResetPassword-DZXXknBi.js
│           ├── RovingFocusGroup-DcCca5gN.js
│           ├── TextLink.vue_vue_type_script_setup_true_lang-DK3B_jX3.js
│           ├── VerifyEmail-B-_RFgAO.js
│           ├── Welcome-C8dAIG49.js
│           ├── app-BGwCcv-l.js
│           ├── app-BaCNvQdL.css
│           └── useForwardExpose-CQNdSgRK.js
│
├── python_scripts/                         # Python Analytics Module
│   ├── .DS_Store
│   ├── dataset.csv                         # Main Dataset
│   ├── read_csv.py                         # Data Processing Script
│   └── venv/                               # Python Virtual Environment
│       ├── .gitignore
│       ├── bin/                            # Python Executables
│       │   ├── Activate.ps1
│       │   ├── activate
│       │   ├── activate.csh
│       │   ├── activate.fish
│       │   ├── f2py
│       │   ├── numpy-config
│       │   ├── pip
│       │   ├── pip3
│       │   ├── pip3.13
│       │   ├── python -> python3.13
│       │   ├── python3 -> python3.13
│       │   └── python3.13
│       ├── include/
│       │   └── python3.13/
│       └── lib/
│           └── python3.13/
│               └── site-packages/          # Python Packages (NumPy, etc.)
│
├── resources/                              # Frontend Resources
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── components/                     # Vue Components
│   │   │   ├── charts/
│   │   │   ├── dashboard/
│   │   │   └── ui/                         # UI Components Library
│   │   │       ├── avatar/
│   │   │       ├── breadcrumb/
│   │   │       ├── button/
│   │   │       ├── card/
│   │   │       ├── checkbox/
│   │   │       ├── collapsible/
│   │   │       ├── dialog/
│   │   │       ├── dropdown-menu/
│   │   │       ├── input/
│   │   │       ├── label/
│   │   │       ├── navigation-menu/
│   │   │       ├── separator/
│   │   │       ├── sheet/
│   │   │       ├── sidebar/
│   │   │       ├── skeleton/
│   │   │       └── tooltip/
│   │   ├── composables/                    # Vue Composables
│   │   ├── layouts/                        # Layout Components
│   │   │   ├── app/
│   │   │   ├── auth/
│   │   │   └── settings/
│   │   ├── lib/                           # Utility Libraries
│   │   ├── pages/                         # Page Components
│   │   │   ├── auth/
│   │   │   ├── settings/
│   │   │   └── students/
│   │   ├── router/                        # Routing
│   │   └── types/                         # TypeScript Definitions
│   └── views/                             # Blade Templates
│       └── app.blade.php
│
├── routes/                                 # Route Definitions
│   ├── api.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
│
├── storage/                                # Storage Directory
│   ├── app/
│   │   ├── private/
│   │   └── public/
│   └── framework/
│       ├── cache/
│       ├── sessions/
│       ├── testing/
│       └── views/
│
└── tests/                                  # Test Files
    ├── Feature/
    │   ├── Auth/
    │   └── Settings/
    └── Unit/

## Key Technologies Used

### Backend
- **Laravel 11.x** - PHP Framework
- **SQLite** - Database
- **Inertia.js** - SPA without API

### Frontend
- **Vue.js 3** - JavaScript Framework
- **TypeScript** - Type Safety
- **Tailwind CSS** - Styling
- **Vite** - Build Tool

### Analytics
- **Python 3.13** - Data Processing
- **NumPy** - Numerical Computing
- **Pandas** - Data Analysis (likely)

### Development Tools
- **ESLint** - Code Linting
- **Prettier** - Code Formatting
- **PHPUnit** - PHP Testing

## Core Features

1. **User Authentication System**
   - Login/Register/Password Reset
   - Email Verification
   - Role-based Access (Admin/Student)

2. **Student Management**
   - Student Registration
   - Profile Management
   - Academic Data Tracking

3. **Dropout Prediction Engine**
   - Python-based Analytics
   - Dataset Processing
   - Prediction Models

4. **Dashboard Interface**
   - Admin Dashboard
   - Student Dashboard
   - Data Visualization

5. **External Data Integration**
   - Dataset Import/Export
   - API Endpoints for Data Exchange

## Database Schema

Based on migration files:
- `users` - System users
- `college_admins` - College administrator data
- `students` - Student records
- `predictions` - Dropout predictions
- `external_datasets` - External data sources
- `cache` - Application caching
- `jobs` - Queue jobs

## API Endpoints

- `/api/college-admin/*` - College admin operations
- `/api/student/*` - Student operations
- Authentication routes for login/register
- Settings routes for profile management
```

This structure shows you have a well-organized full-stack application with clear separation of concerns between the Laravel backend, Vue.js frontend, and Python analytics components.

├── DEMO_SETUP.md
├── PROJECT_STRUCTURE.md
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Api
│   │   │   │   ├── CollegeAdminController.php
│   │   │   │   └── StudentController.php
│   │   │   ├── Auth
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   ├── Settings
│   │   │   │   ├── PasswordController.php
│   │   │   │   └── ProfileController.php
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   └── ExternalDataController.php
│   │   ├── Middleware
│   │   │   ├── HandleAppearance.php
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests
│   │   ├── Auth
│   │   │   └── LoginRequest.php
│   │   └── Settings
│   │   └── ProfileUpdateRequest.php
│   ├── Models
│   │   ├── CollegeAdmin.php
│   │   ├── ExternalDataset.php
│   │   ├── Prediction.php
│   │   ├── Student.php
│   │   └── User.php
│   ├── Providers
│   │   └── AppServiceProvider.php
│   └── Services
│   └── StudentFeatureTransformer.php
├── artisan
├── bootstrap
│   ├── app.php
│   ├── cache
│   │   ├── packages.php
│   │   └── services.php
│   └── providers.php
├── composer.json
├── composer.lock
├── components.json
├── config
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── inertia.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database
│   ├── database.sqlite
│   ├── factories
│   │   └── UserFactory.php
│   ├── migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_07_09_082113_create_college_admins_table.php
│   │   ├── 2025_07_09_082131_create_students_table.php
│   │   ├── 2025_07_09_082152_create_predictions_table.php
│   │   └── 2025_07_09_082209_create_external_datasets_table.php
│   └── seeders
│   └── DatabaseSeeder.php
├── eslint.config.js
├── package-lock.json
├── package.json
├── phpunit.xml
├── python_scripts
│   └── ml_model
│   ├── **init**.py
│   ├── features.py
│   ├── models
│   │   ├── dropout_predictor_model.joblib
│   │   └── label_encoders.joblib
│   ├── predict.py
│   ├── preprocess.py
│   ├── schema
│   │   ├── input_schema.json
│   │   └── output_schema.json
│   ├── service.py
│   └── train_model.py
├── resources
│   ├── css
│   │   └── app.css
│   ├── js
│   │   ├── api
│   │   │   ├── apiClient.ts
│   │   │   ├── collegeAdmins.ts
│   │   │   ├── index.ts
│   │   │   ├── predictions.ts
│   │   │   └── students.ts
│   │   ├── app.ts
│   │   ├── bootstrap.ts
│   │   ├── components
│   │   │   ├── AppLogo.vue
│   │   │   ├── ApplicationLogo.vue
│   │   │   ├── Checkbox.vue
│   │   │   ├── DataTable.vue
│   │   │   ├── DataTableColumnHeader.vue
│   │   │   ├── DataTableViewOptions.vue
│   │   │   ├── DropdownLink.vue
│   │   │   ├── FileUpload.vue
│   │   │   ├── InputError.vue
│   │   │   ├── InputLabel.vue
│   │   │   ├── NavLink.vue
│   │   │   ├── Pagination.vue
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── ResponsiveNavLink.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   ├── TextInput.vue
│   │   │   ├── Toaster.vue
│   │   │   ├── ui
│   │   │   │   ├── alert
│   │   │   │   │   ├── Alert.vue
│   │   │   │   │   ├── AlertDescription.vue
│   │   │   │   │   ├── AlertTitle.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── button
│   │   │   │   │   ├── Button.vue
│   │   │   │   │   ├── index.ts
│   │   │   │   │   └── utils.ts
│   │   │   │   ├── card
│   │   │   │   │   ├── Card.vue
│   │   │   │   │   ├── CardContent.vue
│   │   │   │   │   ├── CardDescription.vue
│   │   │   │   │   ├── CardFooter.vue
│   │   │   │   │   ├── CardHeader.vue
│   │   │   │   │   ├── CardTitle.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── checkbox
│   │   │   │   │   ├── Checkbox.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── dropdown-menu
│   │   │   │   │   ├── DropdownMenu.vue
│   │   │   │   │   ├── DropdownMenuCheckboxItem.vue
│   │   │   │   │   ├── DropdownMenuContent.vue
│   │   │   │   │   ├── DropdownMenuGroup.vue
│   │   │   │   │   ├── DropdownMenuItem.vue
│   │   │   │   │   ├── DropdownMenuLabel.vue
│   │   │   │   │   ├── DropdownMenuPortal.vue
│   │   │   │   │   ├── DropdownMenuRadioGroup.vue
│   │   │   │   │   ├── DropdownMenuRadioItem.vue
│   │   │   │   │   ├── DropdownMenuSeparator.vue
│   │   │   │   │   ├── DropdownMenuShortcut.vue
│   │   │   │   │   ├── DropdownMenuSub.vue
│   │   │   │   │   ├── DropdownMenuSubContent.vue
│   │   │   │   │   ├── DropdownMenuSubTrigger.vue
│   │   │   │   │   ├── DropdownMenuTrigger.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── input
│   │   │   │   │   ├── Input.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── label
│   │   │   │   │   ├── Label.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── select
│   │   │   │   │   ├── Select.vue
│   │   │   │   │   ├── SelectContent.vue
│   │   │   │   │   ├── SelectGroup.vue
│   │   │   │   │   ├── SelectItem.vue
│   │   │   │   │   ├── SelectItemText.vue
│   │   │   │   │   ├── SelectLabel.vue
│   │   │   │   │   ├── SelectScrollDownButton.vue
│   │   │   │   │   ├── SelectScrollUpButton.vue
│   │   │   │   │   ├── SelectSeparator.vue
│   │   │   │   │   ├── SelectTrigger.vue
│   │   │   │   │   ├── SelectValue.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── table
│   │   │   │   │   ├── Table.vue
│   │   │   │   │   ├── TableBody.vue
│   │   │   │   │   ├── TableCaption.vue
│   │   │   │   │   ├── TableCell.vue
│   │   │   │   │   ├── TableEmpty.vue
│   │   │   │   │   ├── TableFooter.vue
│   │   │   │   │   ├── TableHead.vue
│   │   │   │   │   ├── TableHeader.vue
│   │   │   │   │   ├── TableRow.vue
│   │   │   │   │   └── index.ts
│   │   │   │   ├── toast
│   │   │   │   │   ├── Toast.vue
│   │   │   │   │   ├── ToastAction.vue
│   │   │   │   │   ├── ToastClose.vue
│   │   │   │   │   ├── ToastDescription.vue
│   │   │   │   │   ├── ToastProvider.vue
│   │   │   │   │   ├── ToastTitle.vue
│   │   │   │   │   ├── ToastViewport.vue
│   │   │   │   │   ├── index.ts
│   │   │   │   │   └── use-toast.ts
│   │   │   │   └── utils.ts
│   │   ├── lib
│   │   │   ├── columns.ts
│   │   │   └── utils.ts
│   │   ├── layouts
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   ├── pages
│   │   │   ├── Auth
│   │   │   │   ├── ConfirmPassword.vue
│   │   │   │   ├── ForgotPassword.vue
│   │   │   │   ├── Login.vue
│   │   │   │   ├── Register.vue
│   │   │   │   ├── ResetPassword.vue
│   │   │   │   └── VerifyEmail.vue
│   │   │   ├── Dashboard.vue
│   │   │   ├── ExternalData
│   │   │   │   └── Upload.vue
│   │   │   ├── Predictions
│   │   │   │   └── Index.vue
│   │   │   ├── Profile
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Partials
│   │   │   │   ├── DeleteUserForm.vue
│   │   │   │   └── UpdatePasswordForm.vue
│   │   │   ├── Settings
│   │   │   │   ├── CollegeAdmins
│   │   │   │   │   ├── columns.ts
│   │   │   │   │   └── Index.vue
│   │   │   │   ├── data.ts
│   │   │   │   └── schema.ts
│   │   │   ├── Students
│   │   │   │   ├── columns.ts
│   │   │   │   ├── data-table.ts
│   │   │   │   ├── Index.vue
│   │   │   │   ├── schema.ts
│   │   │   │   └── useStudents.ts
│   │   │   └── Welcome.vue
│   │   ├── router
│   │   │   └── index.ts
│   │   └── types
│   │   ├── index.d.ts
│   │   └── ziggy.d.ts
│   └── views
│   ├── app.blade.php
│   └── welcome.blade.php
├── routes
│   ├── api.php
│   ├── auth.php
│   ├── channels.php
│   └── web.php
├── start-demo.sh
├── tests
│   ├── CreatesApplication.php
│   ├── Feature
│   │   ├── Auth
│   │   │   ├── AuthenticationTest.php
│   │   │   ├── EmailVerificationTest.php
│   │   │   ├── PasswordConfirmationTest.php
│   │   │   ├── PasswordResetTest.php
│   │   │   ├── PasswordUpdateTest.php
│   │   │   └── RegistrationTest.php
│   │   ├── ExampleTest.php
│   │   └── ProfileTest.php
│   ├── Pest.php
│   └── Unit
│   └── ExampleTest.php
├── tsconfig.json
└── vite.config.ts
