# API de la librería y papelería

Todas las rutas están bajo `/api`. Salvo `POST /login`, requieren `Authorization: Bearer <token>`; el token se obtiene mediante el flujo Sanctum descrito en la guía adjunta. No hay endpoint público de registro.

## Roles

- **Administrador:** lectura y mantenimiento de autores, categorías, libros y clientes; puede consultar todas las ventas y anularlas.
- **Vendedor:** lectura del catálogo y mantenimiento de clientes; registra ventas y consulta únicamente las ventas que realizó.

## Recursos

| Recurso | Rutas | Campos principales |
| --- | --- | --- |
| Autores | `GET, POST /authors`, `GET, PUT, PATCH, DELETE /authors/{id}` | `name`, `biography` |
| Categorías | `GET, POST /categories`, `GET, PUT, PATCH, DELETE /categories/{id}` | `name`, `description` |
| Libros | `GET, POST /books`, `GET, PUT, PATCH, DELETE /books/{id}` | `isbn`, `title`, `author_id`, `category_id`, `price`, `stock`, `description`, `active` |
| Clientes | `GET, POST /customers`, `GET, PUT, PATCH, DELETE /customers/{id}` | `name`, `email`, `phone`, `address` |
| Ventas | `GET, POST /sales`, `GET /sales/{id}`; administrador: `DELETE /sales/{id}` | `customer_id` opcional, `items: [{book_id, quantity}]` |

Las listas están paginadas (20 registros). Cada venta registra el usuario, precio unitario, cantidad, descuento y subtotal/total. Anular una venta repone existencias.

## Inventario y descuentos

Una venta sólo acepta libros activos y cantidades disponibles. Las filas de inventario se bloquean durante la transacción; si falta existencia, se cancela la venta completa. El descuento se calcula por línea: 5% al comprar de 5 a 9 unidades del mismo libro y 10% al comprar 10 o más. Menos de 5 unidades no tiene descuento. La regla está centralizada en `SaleController` para cambiar fácilmente los umbrales.

## Puesta en marcha

1. Revisar que `.env` apunte a la base de datos de este proyecto.
2. Aplicar las migraciones nuevas con `php artisan migrate` (no usar `migrate:fresh`).
3. Cargar roles y administrador de forma idempotente con `php artisan db:seed`.
4. Credenciales iniciales de práctica: username `Admin`, contraseña `Hola123`. Cambiar la contraseña antes de usar datos reales.

El seeder conserva la contraseña y perfil de un administrador ya existente y asigna su rol al rol `Administrador`. Los seeders de catálogo aún no crean libros ni clientes de ejemplo.
