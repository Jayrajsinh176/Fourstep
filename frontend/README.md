4steos

### Member authentication

The member module includes a sign-in and sign-up flow that talks to the Laravel backend. Make sure to set the API base URL in `.env`:

```
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

Run the Laravel server (`php artisan serve`) and execute `php artisan migrate` in the backend to create the `members` table before trying to register or log in.

Frontend

```
npm install
npm run dev
```

Backend

```
cd backend
composer install
php artisan migrate
php artisan serve
```
