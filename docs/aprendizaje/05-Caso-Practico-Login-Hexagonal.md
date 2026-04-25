# 05. Caso Práctico: Migrando el Login a Hexagonal

Hoy migramos nuestro primer módulo (el Login) a la nueva estructura. Acá tenés el resumen de cómo quedó el código y por qué se diseñó así.

## El Problema Original
Teníamos un archivo `auth_controller.php` con casi 300 líneas.
El código del login hacía esto todo junto:
1. Validaba el Request (email, password).
2. Se conectaba a PostgreSQL para buscar el hash.
3. Si fallaba en DB, se conectaba a Redis (`email_to_id`) por si era un registro asíncrono.
4. Verificaba el hash con `password_verify`.
5. Devolvía el JSON.

**Problema:** Si querías cambiar cómo se validaba un usuario, o usar otra DB, tenías que romper todo. No se podía testear.

## La Solución Hexagonal (En 4 archivos)

Separamos responsabilidades en capas claras:

### 1. Domain (Lo que no cambia)
Creamos una interfaz (Puerto) que dice *qué* necesitamos, no *cómo* hacerlo.
`app/Domain/Ports/AuthRepositoryInterface.php`:
```php
interface AuthRepositoryInterface {
    public function findByEmail(string $email): ?User;
}
```

### 2. Application (El orquestador)
El caso de uso que aplica las reglas de negocio, pero no sabe si usamos Postgres o Redis.
`app/Application/UseCases/Auth/LoginUseCase.php`:
```php
class LoginUseCase {
    public function __construct(private AuthRepositoryInterface $repo) {}

    public function execute(LoginCommand $command): User {
        $user = $this->repo->findByEmail($command->email);
        
        if (!$user || !password_verify($command->password, $user->passwordHash)) {
            throw new Exception("Correo o contraseña incorrectos");
        }
        return $user;
    }
}
```

### 3. Infrastructure (El trabajo sucio)
Acá metimos tu lógica híbrida. Esta clase implementa la interfaz del Dominio.
`app/Infrastructure/Repositories/HybridAuthRepository.php`:
```php
class HybridAuthRepository implements AuthRepositoryInterface {
    public function findByEmail(string $email): ?User {
        // 1. Busca en PostgreSQL
        $dbUser = DB::selectOne("SELECT ... FROM usuarios WHERE email = ?", [$email]);
        if ($dbUser) return new User(...);

        // 2. Fallback: Busca en Redis
        $redisId = Redis::hget('viva:email_to_id', $email);
        if ($redisId) {
            // ... busca data y retorna User
        }

        return null;
    }
}
```

### 4. Presentation (El mesero)
El Controller solo recibe el HTTP, se lo pasa al UseCase y escupe el JSON.
`app/Http/Controllers/Auth/LoginController.php`:
```php
class LoginController extends Controller {
    public function __construct(private LoginUseCase $useCase) {}

    public function store(Request $request) {
        $command = new LoginCommand($request->email, $request->password);
        $user = $this->useCase->execute($command);
        return response()->json(['user' => $user]);
    }
}
```

## Conclusión y Aprendizaje
- **El código es testeable:** Podemos pasarle un repositorio de mentira (Mock) al `LoginUseCase` y probar si el `password_verify` funciona sin levantar una base de datos.
- **La magia del Container:** En el `AppServiceProvider.php` le dijimos a Laravel: *"Cuando alguien pida el AuthRepositoryInterface, dale el HybridAuthRepository"*. 
- **Separación de preocupaciones:** Si mañana dejamos de usar Redis como fallback, solo tocamos UN archivo (`HybridAuthRepository`). El controller y el UseCase ni se enteran.