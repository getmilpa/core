# Empezar

`milpa/core` no arranca nada. Son los contratos y los tipos que el resto de la familia implementa —
sin ORM, sin cliente HTTP, sin aplicación anfitriona. Se instala solo cuando quieres escribir algo
que hable el idioma de Milpa sin arrastrar el runtime.

```bash
composer require milpa/core
```

## Lo que trae, y lo que no

| Trae | No trae |
|---|---|
| interfaces y value objects | implementaciones |
| atributos como `#[PluginMetadata]` | el cargador que los lee |
| el vocabulario de capacidades | el resolvedor que las cruza |

Si buscas una aplicación que corra, eso es [`milpa/framework`](https://github.com/getmilpa/framework)
(`composer create-project milpa/framework mi-app`). `milpa/skeleton` fue esa puerta hasta la 0.14.0 y
quedó abandonado a favor del framework.
Si buscas el kernel, es [`milpa/runtime`](https://github.com/getmilpa/runtime).

## La regla que explica el paquete entero

**Un contrato no puede depender de quien lo cumple.** Por eso este paquete no tiene dependencias de
producción más allá de PHP: el día que las tuviera, cualquiera que quiera hablar el idioma tendría que
aceptar también la implementación de alguien más.

Eso hace al core aburrido a propósito. La emoción vive en los paquetes que lo consumen.
