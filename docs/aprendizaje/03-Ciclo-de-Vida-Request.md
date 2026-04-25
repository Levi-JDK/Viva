# 03. El Ciclo de Vida de una Request (De la A a la Z)

Imaginá que un usuario hace click en "Iniciar Sesión" en VIVA. ¿Qué pasa desde que hace click hasta que ve el dashboard?

## Paso a Paso

### 1. El usuario toca el botón (HTTP Request)
El browser envía un pedido al servidor:
```
POST /api/login HTTP/1.1
Host: viva.test
Content-Type: application/json

{"email": "juan@email.com", "password": "123456"}
```

### 2. El servidor web (Apache/Nginx) recibi el pedido
El servidor sabe que `.php` debe ejecutarse. Pasa el control a `public/index.php` (el punto de entrada de Laravel).

### 3. public/index.php (El bootstrapping)
```php
// Esto es lo que hace Laravel internamente
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send();
$kernel->terminate($request, $response);
```
Este archivo:
- Crea la aplicación de Laravel
- Instancia el **Kernel HTTP** (la lógica central)

### 4. El Http Kernel (Las reglas globales)
En `app/Http/Kernel.php` definimos los **Middleware** (filtros globales):
- `HandleCors` (permite o bloquea requests de otros orígenes)
- `CheckForMaintenanceMode` (si la app está en mantenimiento)
- **`Authenticate`** (nuestra Patovica JWT)
- `TrimStrings`, `ConvertEmptyStringsToNull` (limpieza)

Si la request pasa todos los filtros, llega al **Router**.

### 5. El Router (El recepcionista)
El Router lee la URL y el método:
```php
// routes/api.php
Route::post('/login', [LoginController::class, 'store']);
```
Dice: *"¡Busquen al LoginController y ejecuten el método store!"*

### 6. El Controller (El mesero)
El Controller recibe la request, la pasa al UseCase y responde:

```php
class LoginController {
    public function __construct(private LoginUseCase $useCase) {}
    
    public function store(LoginRequest $request): JsonResponse
    {
        $command = LoginCommand::fromRequest($request);
        $user = $this->useCase->execute($command);
        
        return response()->json(['user' => $user], 200);
    }
}
```

### 7. El UseCase (La cocina)
Acá ocurre la lógicaorchestration. El UseCase no calcula, solo orquesta:

```php
class LoginUseCase {
    public function __construct(
        private AuthRepositoryInterface $authRepo,
        private JwtService $jwtService
    ) {}
    
    public function execute(LoginCommand $command): User
    {
        $user = $this->authRepo->findByEmail($command->email);
        
        if (!$user || !$this->verifyPassword($command->password, $user)) {
            throw new InvalidCredentialsException();
        }
        
        $token = $this->jwtService->generateToken($user);
        
        return $user->withToken($token);
    }
}
```

### 8. La Infrastructure (La alacena y los cooks)
El Repository va a PostgreSQL, ejecuta tu Stored Procedure:
```php
class PostgresAuthRepository implements AuthRepositoryInterface {
    public function findByEmail(string $email): ?User {
        // Acá se llama a tu función PL/pgSQL
        $row = DB::selectOne("SELECT * FROM get_user_by_email(?)", [$email]);
        return $row ? User::fromArray((array) $row) : null;
    }
}
```

### 9. El retorno (De vuelta al cliente)
La respuesta baja por el mismo camino:
```
HTTP/1.1 200 OK
Content-Type: application/json

{"user": {"id": 1, "email": "juan..."}, "token": "eyJhbG..."}
```

## Resumen Visual

```
Usuario
   │
   ▼
Apache/Nginx ──────► public/index.php
   │
   ▼
Http Kernel (Middleware) ───► ¿Pasas los filtros?
   │                             │
   │                          [SI]
   ▼                             ▼
Router ─────────────────── Controller
   │                             │
   ▼                             ▼
UseCase ─────────────────── Service
   │                             │
   ▼                             ▼
Domain Ports ───────────── Infrastructure
   │                             │
   ▼                             ▼
PostgreSQL/Redis ◄──► Respuesta
   │
   ▼
Usuario (respuesta)
```

## En nuestro proyecto específico

Recordá la regla de arquitectura: **El Controller y el UseCase son DELGADOS. La lógica está en PostgreSQL.**