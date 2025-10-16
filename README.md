<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
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

## API Endpoints

### Authentication

- POST `api/auth/register`
  - Body: JSON
    - name (string, required)
    - email (string, required, unique)
    - password (string, required)
    - type (string: "admin" | "sales", required)
    - branch_id (int, required)
  - 201 Response: `{ "message": "User registered successfully", "user": { ... }, "token": "..." }`

- POST `api/auth/login`
  - Body: JSON { email, password }
  - 200 Response: `{ "message": "Login successful", "user": { ... }, "token": "..." }`
  - 401 Response: `{ "message": "Invalid email or password" }`

- POST `api/auth/logout` (auth required)
  - 200 Response: `{ "message": "Logged out successfully" }`

- GET `api/auth/me` (auth required)
  - 200 Response: `{ "user": { ... } }`

### Leads (auth required)

- GET `api/leads`
  - Query: `status`, `branch_id`, `user_id` (admin only), `per_page`
  - 200 Response: Paginated list under `data` including `user` and `branch` relations

- GET `api/leads/{lead}`
  - 200 Response: Single lead resource with relations

- POST `api/leads`
  - Body: JSON { name, phone, branch_id }
  - Behavior: Auto-assigns to next sales user (round-robin) and queues email
  - 201 Response: `{ "message": "Lead created successfully", "lead": { id, name, phone, status, user_id, branch_id, user, branch } }`
  - Errors: 403 unauthorized; 500 `{ "message": "Lead creation failed" }`

- PUT `api/leads/{lead}`
  - Body: JSON { name?, phone?, status?, user_id? (ignored for sales) }
  - 200 Response: Updated lead resource
  - Errors: 403 unauthorized; unexpected errors logged

- DELETE `api/leads/{lead}`
  - 200 Response: `{ "message": "Lead deleted successfully" }`

### Branches (admin only, auth required)

- GET `api/branches/{branch}/summary`
  - 200 Response: `{ total_leads, new, in_progress, closed, top_sales: [ { user, leads } ] }`
  - 403 Response: `{ "message": "You don't have permission to perform this action." }`

- POST `api/branches/{branch}/clear-cache`
  - Behavior: Clears summary cache and resets round-robin index for the branch
  - 200 Response: `{ "message": "Branch cache cleared" }`
  - Errors: 403 forbidden; 500 `{ "message": "Failed to clear cache" }`

### Auth details

- All protected routes use Laravel Sanctum bearer tokens.
- Header: `Authorization: Bearer <token>`

### Error responses

- 401: Invalid credentials
- 403: Forbidden (policies/gates)
- 422: Validation errors (FormRequests)
- 500: Server errors with message; details logged

### Curl examples

```bash
# Login
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"secret"}'

# Create lead
curl -X POST http://localhost/api/leads \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","phone":"1234567890","branch_id":1}'

# Clear branch cache (admin)
curl -X POST http://localhost/api/branches/1/clear-cache \
  -H "Authorization: Bearer <TOKEN>"
```

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
