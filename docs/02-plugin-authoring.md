# Escribir un plugin

Un plugin de Milpa es una clase que declara qué **provee** y qué **requiere**, y el runtime resuelve
el orden. No hay registro central que editar ni archivo de configuración que sincronizar.

## Lo mínimo

```php
use Milpa\Attributes\PluginMetadata;
use Milpa\Interfaces\Plugin\PluginInterface;
use Milpa\Plugin\PluginBase;

#[PluginMetadata(version: '1.0.0', author: 'Tú', name: 'MiPlugin', type: 'Service')]
final class MiPlugin extends PluginBase implements PluginInterface
{
    public function boot(): void { }
    public function install(): void { }
    public function uninstall(): void { }
    public function enable(): void { }
    public function disable(): void { }
}
```

## Capacidades: el contrato entre plugins

Declarar `provides` y `requires` en el `milpa.json` es lo que deja que dos plugins se encuentren sin
conocerse. El plugin A publica `StorageCapability`, el B la pide, y el runtime falla al arrancar —no
en producción a las tres de la mañana— si nadie la provee.

> Una capacidad requerida y ausente es un error de arranque, no una excepción en tiempo de ejecución.
> Ésa es toda la diferencia entre enterarse hoy y enterarse cuando un usuario lo encuentre.

## Lo que el runtime NO va a hacer por ti

- **No adivina el orden.** Si hay un ciclo entre capacidades, lo dice y se detiene: inventar un orden
  sería elegir por ti sin decírtelo.
- **No inyecta lo que no declaraste.** Un servicio que no pediste no aparece.
- **No esconde una capacidad ausente.** Una sección, un comando o una herramienta que dependía de
  algo que no está simplemente no existe — en vez de existir y fallar al usarse.

## Dónde seguir

La referencia de cada tipo está en la navegación de la izquierda. Para ver el ciclo completo
—capacidad provista, capacidad consumida, herramienta expuesta a un agente, aprobación humana— el
ejemplo corrible es [`example-agent-ready-blog`](https://github.com/getmilpa/example-agent-ready-blog).
