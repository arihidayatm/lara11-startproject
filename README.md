Tutorial Starting Project in Laravel 11 use Breeze
------------------------------------------------
## 1. Create Project

- create Project

``composer create-project laravel/laravel lara11-startproject``

- Install Breeze

``composer require laravel/breeze --dev``

``php artisan breeze:install``

    ``blade``

    ``no``

    ``1``

``php artisan serve``

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


``php artisan migrate``

--------------------------------------------------------------

## 3. Seeders and Factories Demo User Data

- Seeders

``php artisan make:seeder UsersTableSeeder``

    public function run(): void
    {
        DB::table('users')->insert([
        //superadmin
          [
              'name' => 'Super Admin',
              'username' => 'superadmin',
              'email' => 'superadmin@mahdev.com',
              'password' => Hash::make ('qwerty123'),
              'role' => 'superadmin',
              'status' => 'active',
        ],
        //admin
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@mahdev.com',
                'password' => Hash::make ('qwerty123'),
                'role' => 'admin',
                'status' => 'active',
            ],
        //user
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


// seeders/DatabaseSeeder.php

    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        User::factory(10)->create();
    }

``php artisan migrate``

``php artisan migrate:fresh --seed``

--------------------------------------------------------------

## 4. Super Admin, Admin and User Multi Login System
(Multi Auth with Breeze Login Auth for Super Admin, Admin and User)

``php artisan make:controller SuperAdminController``

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

``php artisan make:controller AdminController``

//Controller/AdminController.php

    public function AdminDashboard(Request, $request)
    {
        return view ('admin.dashboard');
    }

//routes/web.php

    Route::get('admin/dashboard',[AdminController::class,'AdminDashboard'])->name('admin.dashboard');

create folder at admin/dashboard.blade.php

//views/admin/dashboard.blade.php

``php artisan make:controller UserController``

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

//resources/views/auth/login.blade.php


    {{-- Use login with Email/Name/Phone --}}
    <div>
        <x-input-label for="login" :value="__('Email')" />
        <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
        <x-input-error :messages="$errors->get('login')" class="mt-2" />
    </div>

//Http/Requests/Auth/LoginRequest.php


    use App\Models\User;

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->login)
                    ->orWhere('name', $this->login)
                    ->orWhere('phone', $this->login)
                    ->first();

        if (!$user || !Hash::check($this->password, $user->password))
        {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

If you want login use login with Email/Name/Phone.

Download or clone this Branc :
[Login-with-Email-Name-Phone](https://github.com/arihidayatm/lara11-startproject/tree/Login-with-Name-and-Phone)

------------------------------------------------------------------
## 6. Admin Template Setup

If you want Admin Template Setup,

- NobleUI - HTML Bootstrap 5 Admin Dashboard Template

download or clone this Branch :
[Admin-Template-Setup](https://github.com/arihidayatm/lara11-startproject/tree/Admin-Template-Setup)

------------------------------------------------------------------
## 6.1 Dashboard Page Segmentation

If you want Dashboard Page Segmentation in SuperAdmin and Admin,
create folder _body at resources/views/sadmin/_body
and create file blade.php at _body for segmentation page
--sidebar.blade.php
--navbar.blade.php
--footer.blade.php

and <body> in dashboard.blade.php at sadmin folder or admin folder
insert this code:

    <body>
        <div class="main-wrapper">

            <!-- partial:partials/_sidebar.html -->
            @include('sadmin._body.sidebar')
            <!-- partial -->

            <div class="page-wrapper">

                <!-- partial:partials/_navbar.html -->
                @include('sadmin._body.navbar')
                <!-- partial -->
                
                <!-- content -->
                @yield('sadmin')
                <!-- content -->

                <!-- partial:partials/_footer.html -->
                @include('sadmin._body.footer')
                <!-- partial -->
            
            </div>
        </div>

        <!-- core:js -->
        <script src="{{ asset('assets/vendors/core/core.js') }}"></script>
        <!-- endinject -->

        <!-- Plugin js for this page -->
        <script src="{{ asset('assets/vendors/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
        <!-- End plugin js for this page -->

        <!-- inject:js -->
        <script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/template.js') }}"></script>
        <!-- endinject -->

        <!-- Custom js for this page -->
        <script src="{{ asset('assets/js/dashboard-dark.js') }}"></script>
        <!-- End custom js for this page -->

    </body>


------------------------------------------------------------------

## 6.2 SuperAdmin Logout Option

//resources/views/sadmin/_body/navbar.blade.php
navbar.blade.php

    <li class="dropdown-item py-2">
        <a href="{{ route('sadmin.logout') }}" class="text-body ms-0">
            <i class="me-2 icon-md" data-feather="log-out"></i>
            <span>Log Out</span>
        </a>
    </li>

//app/Http/Controllers/SuperAdminController.php
SuperAdminController.php

    public function SuperAdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

//routes/web.php
web.php

    Route::get('sadmin/logout',[SuperAdminController::class,'SuperAdminLogout'])->name('sadmin.logout');

------------------------------------------------------------------

## 6.3 Costumize Login Form Super Admin

Create file sadmin-login.blade.php at resources/views/sadmin
//resources/views/sadmin/sadmin-login.blade.php
sadmin/sadmin-login.blade.php

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Responsive HTML Admin Dashboard Template based on Bootstrap 5">
        <meta name="author" content="MAHUI">
        <meta name="keywords" content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

        <title>Starter Project - Super Admin Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->

        <!-- core:css -->
        <link rel="stylesheet" href="{{ asset('assets/vendors/core/core.css') }}">
        <!-- endinject -->

        <!-- Plugin css for this page -->
        <!-- End plugin css for this page -->

        <!-- inject:css -->
        <link rel="stylesheet" href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
        <!-- endinject -->

    <!-- Layout styles -->  
        <link rel="stylesheet" href="{{ asset('assets/css/demo2/style.css') }}">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    </head>
    <body>
        <div class="main-wrapper">
            <div class="page-wrapper full-page">
                <div class="page-content d-flex align-items-center justify-content-center">

                    <div class="row w-100 mx-0 auth-page">
                        <div class="col-md-8 col-xl-6 mx-auto">
                            <div class="card">
                                <div class="row">
                                    <div class="col-md-4 pe-md-0">
                                        <div class="auth-side-wrapper">

                                        </div>
                                    </div>
                                    <div class="col-md-8 ps-md-0">
                                        <div class="auth-form-wrapper px-4 py-5">
                                            <a href="#" class="noble-ui-logo logo-light d-block mb-2">MAH<span>UI</span></a>
                                            <h5 class="text-muted fw-normal mb-4">Welcome back! Log in to your account Super Admin.</h5>
                                            <form class="forms-sample" method="POST" action="{{ route('login') }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="login" class="form-label">Email address</label>
                                                    <input type="text" name="email" class="form-control" id="login" placeholder="Email" value="{{ old('email') }}" required>
                                                    <span class="text-danger">@error('email'){{ $message }}@enderror</span>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input type="password" name="password" class="form-control" id="password" autocomplete="password" placeholder="Password" value="{{ old('password') }}" required>
                                                    <span class="text-danger">@error('password'){{ $message }}@enderror</span>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="authCheck">
                                                    <label class="form-check-label" for="authCheck">
                                                    Remember me
                                                    </label>
                                                </div>
                                            <div>
                                                <button type="submit" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
                                                {{-- <i class="btn-icon-prepend" data-feather="twitter"></i> --}}
                                                Login
                                                </button>
                                            </div>
                                            <a href="{{ route('register') }}" class="d-block mt-3 text-muted">Not a user? Sign up</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- core:js -->
        <script src="{{ asset('assets/vendors/core/core.js') }}"></script>
        <!-- endinject -->

        <!-- Plugin js for this page -->
        <!-- End plugin js for this page -->

        <!-- inject:js -->
        <script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/template.js') }}"></script>
        <!-- endinject -->

        <!-- Custom js for this page -->
        <!-- End custom js for this page -->

    </body>
    </html>

//app/Http/Controllers/SuperAdminController.php
SuperAdminController.php

    public function SuperAdminLogin(Request $request)
    {
        return view ('sadmin.sadmin-login');
    }

//routes/web.php
web.php

    //Super Admin Login
    Route::get('sadmin/login',[SuperAdminController::class,'SuperAdminLogin'])->name('sadmin.login');


------------------------------------------------------------------
## Contributing

The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs).
- Laravel and Breeze: [Laravel Starter Kits - Breeze and Blade](https://laravel.com/docs/11.x/starter-kits#breeze-and-blade)
- Spatie: [Spatie Laravel Permission Installation](https://spatie.be/docs/laravel-permission/v6/installation-laravel)
- Tailwind CSS: [Tailwind CSS Laravel Installation](https://tailwindcss.com/docs/guides/laravel)
- Tailwind Dashboard Template: [Tailwind Dashboard Template Source](https://www.tailwindawesome.com/resources/dashboard-template)


## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Muhamad Ari Hidayat via [28000mah@gmail.com](mailto:28000mah@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
