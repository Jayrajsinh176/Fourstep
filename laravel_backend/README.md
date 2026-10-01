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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

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

## Initial main member (required for referral links)

To enable referral links immediately you should create one active "main" member record in the `members` table. This user will act as an initial sponsor so your public referral links contain a valid `placement_id`.

Recommended: use Tinker to create the member (will generate `user_id` automatically):

```bash
cd backend
php artisan tinker

# inside tinker
use App\Models\Member;
use Illuminate\Support\Facades\Hash;

Member::create([
	'fullname' => 'Main Sponsor',
	'dob' => '1980-01-01',
	'gender' => 'other',
	'mobile_no' => '9999999999',
	'email' => 'main@example.com',
	'password' => Hash::make('ChangeMe123'),
	'status' => 1, // active
]);
```

When created, check the created `user_id` and use it as the `placement_id` in the referral links shown in the dashboard. You can also activate any existing member by setting `status = 1` and optionally `activation_date = now()` in the DB.

If you prefer SQL, insert a row into `members` and set `status` to `1` after generating a bcrypt password hash.

### Quick: create main member with seeder

You can create/update the main developer member automatically with the seeder. Optionally set these env vars in `backend/.env` to control the seeded values:

- `MAIN_MEMBER_USER_ID` (default `MAINDEV001`)
- `MAIN_MEMBER_MOBILE` (default `9999999999`)
- `MAIN_MEMBER_EMAIL` (default `main@example.com`)
- `MAIN_MEMBER_PASSWORD` (default `ChangeMe123`)
- `MAIN_MEMBER_FULLNAME`, `MAIN_MEMBER_DOB`, `MAIN_MEMBER_GENDER`

Run the seeder:

```bash
cd backend
php artisan db:seed --class=\Database\Seeders\MainMemberSeeder
```

Or run all seeders (includes the main member):

```bash
php artisan db:seed
```
