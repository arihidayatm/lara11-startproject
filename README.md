Tutorial Starting Project in Laravel 11 use Breeze
------------------------------------------------
## 1. Create Project

- create Project

    composer create-project laravel/laravel lara11-startproject

- Install Breeze

    composer require laravel/breeze --dev

    php artisan breeze:install

    blade

    no

    1

    php artisan serve

------------------------------------------------------------

## 2. Create Database, configuration and migrate

- Create Database

- Configuration

// .env

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=projectstarter
    DB_USERNAME=root
    DB_PASSWORD=********

- Migrate

// migration/0001_01_01_000000_create_users_table.php

    {
        Schema::create('users', function (Blueprint $table){
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('photo')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('role',['superadmin','admin','user'])->default('user');
            $table->enum('status',['active','inactive'])->default('active');
            $table->rememberToken();
            $table->timestamp();
        });
    }

// Models/User.php

	protected $guarded = [];


    php artisan migrate

--------------------------------------------------------------

## 3. Seeders and Factories Demo User Data

- Seeders

    php artisan make:seeder UsersTableSeeder

    public function run(): void
    {
        DB::table('users')->insert([
        // superadmin
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'superadmin',
                'status' => 'active',
            ],
        // admin
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'admin',
                'status' => 'active',
            ],
        // user
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'user',
                'status' => 'active',
            ]
        ]);
    }

- Factories

// factories/UserFactory.php
``
    public function definition(): array
    {
        return[
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('qwerty123'),
            'phone' => fake()->phoneNumber,
            'address' => fake()->address(),
            'photo' => fake()->imageUrl('60','60'),
            'role' => fake()->randomElement(['superadmin','admin','user']),
            'status' => fake()->randomElement(['active','inactive']),
            'remember_token' => Str::random(10),
        ];
    }
``

// seeders/DatabaseSeeder.php

    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        User::factory(10)->create();
    }

php artisan migrate
php artisan migrate:fresh --seed

--------------------------------------------------------------

## 4. Super Admin, Admin and User Multi Login System
(Multi Auth with Breeze Login Auth for Super Admin, Admin and User)

php artisan make:controller SuperAdminController

//Controller/SuperAdminController.php

    public function SuperAdminDashboard(Request, $request)
    {
        return view ('sadmin.dashboard');
    }

//routes/web.php

    require __DIR__.'/auth.php';

    Route::get('sadmin/dashboard',[SuperAdminController::class,'SuperAdminDashboard'])->name('sadmin.dashboard');

create folder at sadmin/dashboard.blade.php
//views/sadmin/dashboard.blade.php


php artisan make:controller AdminController

//Controller/AdminController.php

    public function AdminDashboard(Request, $request)
    {
        return view ('admin.dashboard');
    }

//routes/web.php
    Route::get('admin/dashboard',[AdminController::class,'AdminDashboard'])->name('admin.dashboard');

create folder at admin/dashboard.blade.php
//views/admin/dashboard.blade.php


php artisan make:controller UserController


- Multi Login System
//Controllers/Auth/AuthenticatedSessionController.php

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        if($request->user()->role == 'superadmin')
        {
            return redirect()->intended('sadmin/dashboard');
        }elseif($request->user()->role == 'admin')
        {
            return redirect()->intended('admin/dashboard');
        }elseif($request->user()->role == 'user')
        {
            return redirect()->intended(route('dashboard', absolute: false));
        }
    }


- Role
//Middleware/Role.php


use App\Http\Middleware\Role;

    public function handle(Request $request, Closure $next, $role): Response
    {
        if($request->user()->role !== $role){
            return redirect('dashboard');
        }
        return $next($request);
    }


//bootstrap/app.php

use App\Http\Middleware\Role;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => Role::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


//routes/web.php

    require __DIR__.'/auth.php';

    Route::middleware(['auth', 'role:superadmin'])->group(function(){
        Route::get('sadmin/dashboard',[SuperAdminController::class,'SuperAdminDashboard'])->name('sadmin.dashboard');
    });

    Route::middleware(['auth', 'role:admin'])->group(function(){
        Route::get('admin/dashboard',[AdminController::class,'AdminDashboard'])->name('admin.dashboard');
    });

------------------------------------------------------------------

## 5. Login with Name, Email and Phone


## 6. Admin Template Setup

If you use Admin Template Setup, 
-NobleUI - HTML Bootstrap 5 Admin Dashboard Template

download or clone this Branch :


------------------------------------------------------------------
## Contributing

The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs).


## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Muhamad Ari Hidayat via [28000mah@gmail.com](mailto:28000mah@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
