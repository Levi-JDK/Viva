# 01. ¿Qué es Laravel y su "Service Container"?

Si venís de PHP Vanilla, estás acostumbrado a hacer esto:

```php
require_once 'database.php';
require_once 'AuthHelper.php';

$db = new Database();
$auth = new AuthHelper($db);
```
Vos eras el **responsable de crear las cosas y conectarlas**.

## ¿Qué es Laravel?
Laravel no es solo un montón de funciones pre-hechas (como un framework chico). Laravel es, en su núcleo, una **Caja Mágica (IoC Container)** que se encarga de crear e inyectar objetos por vos.

## El Service Container (La Caja Mágica)

Imaginá que tu aplicación es un restaurante.
En PHP Vanilla, cuando el mozo (Controller) necesita cocinar, él mismo tiene que ir a comprar los ingredientes, prender el fuego y armar el plato.

Con el **Service Container** de Laravel, el mozo solo grita: *"¡Necesito un Cocinero que sepa hacer pizza!"* y el restaurante (Laravel) le entrega mágicamente a un cocinero listo para trabajar.

### ¿Cómo se ve esto en código?

**PHP Vanilla:**
```php
class OrderController {
    public function store() {
        // Yo mismo instancio mi conexión, mi repo, mi caso de uso...
        $pdo = new PDO(...);
        $repo = new OrderRepository($pdo);
        $useCase = new CreateOrderUseCase($repo);
        
        $useCase->execute();
    }
}
```

**Laravel:**
```php
class OrderController {
    // Solo declaro que NECESITO el caso de uso
    public function __construct(private CreateOrderUseCase $useCase) {}
    
    public function store() {
        // Simplemente lo uso. Laravel lo instanció por mí.
        $this->useCase->execute();
    }
}
```

### ¿Cómo sabe Laravel cómo crear el `CreateOrderUseCase`?
Porque Laravel lee los parámetros del `__construct`. Ve que necesita `CreateOrderUseCase`. Va y mira qué necesita esa clase. Ve que necesita `OrderRepository`. Va y lo crea. Usa **Reflection API** de PHP para "leer" tu código y armar el rompecabezas solo.

## Inyección de Dependencias (Dependency Injection - DI)
Esto que acabamos de ver se llama Inyección de Dependencias.
- **Inyección**: Porque alguien más te "inyecta" (te pasa) lo que necesitás.
- **Dependencia**: El objeto que necesitás para trabajar.

### ¿Por qué es genial para VIVA?
Porque nosotros decidimos usar **Interfaces (Ports)**. Le vamos a decir a Laravel:
*"Laravel, cada vez que alguien pida un `OrderRepositoryInterface`, entregale un `PostgresOrderRepository`"*.

Se hace en un archivo llamado `ServiceProvider`:
```php
$this->app->bind(
    OrderRepositoryInterface::class,
    PostgresOrderRepository::class
);
```
Si mañana cambiás de base de datos a MongoDB, solo cambiás ESA LÍNEA y toda tu aplicación usa el nuevo código. No tenés que ir controller por controller cambiando `new Postgres...` por `new Mongo...`.

## Resumen de la clase
1. No hagas `new Objeto()` adentro de tus clases. Pedilos en el `__construct`.
2. Laravel lee tu `__construct` y te inyecta lo que pedís (Service Container).
3. Esto permite "desacoplar" el código, lo que es la base de la Arquitectura Hexagonal.