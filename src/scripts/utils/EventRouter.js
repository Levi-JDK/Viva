/**
 * EventRouter.js
 * 
 * ¿Qué es Event Delegation (Delegación de Eventos)?
 * ------------------------------------------------
 * En lugar de agregar un `addEventListener` a cada botón o elemento de la página
 * (lo cual consume mucha memoria y requiere volver a atar eventos si el DOM cambia),
 * aprovechamos el "Event Bubbling" (burbujeo de eventos) de JavaScript.
 * 
 * Cuando haces clic en un elemento, el evento "burbujea" hacia arriba en el árbol DOM,
 * pasando por sus padres hasta llegar al `document.body`. 
 * 
 * Este EventRouter se "sienta" en el `document.body` y escucha TODOS los clics.
 * Cuando detecta un clic, revisa si el elemento clickeado (o alguno de sus ancestros)
 * tiene un atributo especial llamado `data-action`. Si lo encuentra, busca en su
 * registro interno qué función (callback) debe ejecutar para esa acción y la llama.
 * 
 * Beneficios:
 * 1. Mejor rendimiento: Solo 1 event listener en toda la app.
 * 2. Elementos dinámicos: Si agregas nuevo HTML con JS, los clics funcionarán 
 *    automáticamente sin tener que hacer `.addEventListener()` de nuevo.
 */

class EventRouter {
  constructor() {
    // Almacena las acciones registradas y sus callbacks asociados
    // Ejemplo: { 'user-login': callbackFn, 'item-delete': callbackFn }
    this.routes = {};
    // Evita inicializar múltiples veces
    this.initialized = false;
  }

  /**
   * Registra una acción y la función que debe ejecutarse cuando ocurra.
   * @param {string} actionName - El valor del atributo data-action (ej: 'submit-form')
   * @param {function} callback - La función a ejecutar, recibe el evento y el elemento
   */
  register(actionName, callback) {
    if (typeof callback !== 'function') {
      console.error(`[EventRouter] El callback para la acción '${actionName}' debe ser una función.`);
      return;
    }
    this.routes[actionName] = callback;
  }

  /**
   * Inicializa el router agregando los event listeners al body.
   */
  init() {
    if (this.initialized) return;

    // Escuchamos todos los clics en el body
    document.body.addEventListener('click', (event) => {
      this.handleEvent(event, '[data-action], [data-event]');
    });

    // Escuchamos todos los cambios (selects, inputs) en el body
    document.body.addEventListener('change', (event) => {
      this.handleEvent(event, '[data-action], [data-event]');
    });

    // Escuchamos envíos de formularios en el body
    document.body.addEventListener('submit', (event) => {
      this.handleEvent(event, '[data-action], [data-event]');
    });

    document.body.addEventListener('input', (event) => {
      this.handleEvent(event, '[data-action], [data-event]');
    });

    document.body.addEventListener('keydown', (event) => {
      this.handleEvent(event, '[data-action], [data-event]');
    });

    this.initialized = true;

  }

  /**
   * Manejador genérico de eventos para delegación.
   */
  handleEvent(event, selector) {
    const actionElement = event.target.closest(selector);
    if (!actionElement) return;

    let actionName = actionElement.getAttribute('data-action');
    const dataEvent = actionElement.getAttribute('data-event');

    if (dataEvent) {
      actionName = null; // Reset default actionName since we are using data-event mapping
      const events = dataEvent.split(',').map(e => e.trim());
      for (const evt of events) {
        const [evtType, evtAction] = evt.split(':');
        if (evtType === event.type) {
          actionName = evtAction;
          break;
        }
      }
    } else if (actionName) {
      // Default event mapping for data-action based on element tag
      const tagName = actionElement.tagName.toLowerCase();
      let expectedEvent = 'click';
      
      if (tagName === 'form') {
        expectedEvent = 'submit';
      } else if (tagName === 'select') {
        expectedEvent = 'change';
      } else if (tagName === 'input') {
        const type = actionElement.type.toLowerCase();
        if (type === 'file' || type === 'checkbox' || type === 'radio') {
          expectedEvent = 'change';
        } else if (type === 'submit' || type === 'button' || type === 'reset') {
          expectedEvent = 'click';
        } else {
          expectedEvent = 'input';
        }
      } else if (tagName === 'textarea') {
        expectedEvent = 'input';
      }
      
      if (event.type !== expectedEvent) {
        return; // Ignore events that don't match the expected default event
      }
    }

    if (!actionName) return;

    const routeHandler = this.routes[actionName];

    if (routeHandler) {
      // Pasamos el evento y el elemento que disparó la acción
      routeHandler(event, actionElement);
    } else {
      // Solo logueamos advertencia para clics, ya que 'change' puede dispararse en elementos no registrados
      if (event.type === 'click') {
        console.warn(`[EventRouter] Acción de clic no manejada detectada: '${actionName}'`);
      }
    }
  }
}

// Exportamos un Singleton para que toda la app comparta el mismo router y registro de eventos
export const eventRouter = new EventRouter();