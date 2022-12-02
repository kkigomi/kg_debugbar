(function ($) {
  const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

  const TableWidget = (PhpDebugBar.Widgets.TableWidget =
    PhpDebugBar.Widget.extend({
      tagName: 'table',
      className: csscls('table'),

      initialize(options) {
        if (!options['itemRenderer']) {
          options['itemRenderer'] = this.itemRenderer;
        }

        this.set(options);
      },

      render() {
        this.bindAttr('data', function () {
          let data = this.get('data');
          let itemRenderer = this.get('itemRenderer');

          if (data.head) {
            const tableRow = $('<tr>');
            data.head.forEach((element) => {
              const headItem = $('<th>').text(element);
              headItem.appendTo(tableRow);
            });
            tableRow.appendTo(this.$el);
          }

          if (data.body) {
            const tableRow = this.get('itemRenderer')(this.$el, data.body);
            // tableRow.appendTo(this.$el);
          }
        });
      },

      /**
       * Renders the content of a <li> element
       *
       * @param {jQuery} li The <li> element as a jQuery Object
       * @param {Object} value An item from the data array
       */
      itemRenderer: function ($table, value) {
        value.forEach((element) => {
          const $tableRow = $('<tr>');
          element.forEach((i) => {
            $('<td>').text(i).appendTo($tableRow);
          });
          $tableRow.appendTo($table);
        });
      },
    }));

  const HookWidget = (PhpDebugBar.Widgets.HookWidget =
    PhpDebugBar.Widget.extend({
      className: csscls('hook'),

      initialize(options) {
        if (!options['itemRenderer']) {
          options['itemRenderer'] = this.itemRenderer;
        }
        this.set(options);
      },

      render() {
        this.$status = $(
          '<h1>Event (<em class="event-count" />) | Replace (<em class="replace-count" />)</h1>'
        ).addClass(csscls('event-status'));
        this.$eventHeader = $('<br /><h2>Event</h2>').addClass(
          csscls('event-head')
        );
        this.$eventBody = $('<div />').addClass(csscls('event'));
        this.$replaceHeader = $('<br /><h2>Replace</h2>').addClass(
          csscls('event')
        );
        this.$replaceBody = $('<div />').addClass(csscls('event'));

        this.$status.appendTo(this.$el).show();
        this.$eventHeader.appendTo(this.$el).show();
        this.$eventBody.appendTo(this.$el).show();
        this.$replaceHeader.appendTo(this.$el).show();
        this.$replaceBody.appendTo(this.$el).show();

        this.bindAttr('data', function (data) {
          $('<em>' + data.total.event + '</em>').appendTo(this.$eventHeader);
          $('<em>' + data.total.replace + '</em>').appendTo(
            this.$replaceHeader
          );

          this.$status.find('.event-count').text(data.total.event);
          this.$status.find('.replace-count').text(data.total.replace);

          Object.values(data.event).forEach((item) => {
            const $itemContainer = $('<div />')
              .addClass(csscls('hook-item'))
              .appendTo(this.$eventBody);

            if (item.listener.length) {
              this.get('itemRenderer')($itemContainer, item);
            }
          });

          Object.values(data.replace).forEach((item) => {
            const $itemContainer = $('<div />')
              .addClass(csscls('hook-item'))
              .appendTo(this.$replaceBody);

            if (item.listener?.length) {
              this.get('itemRenderer')($itemContainer, item);
            }
          });
        });
      },
      itemRenderer($itemContainer, item) {
        const $elementItem = $(
          '<h3>' + item.tag + ' <em>(' + item.called + ')</em></h3>'
        );

        $elementItem.appendTo($itemContainer);

        if (item.listener?.length) {
          const body = [];

          item.listener.forEach((element) => {
            let message = '';
            if (element.actualArgumentCount > element.argumentsCount) {
              message = '받은 parameter 보다 더 많이 사용됨. 일부 parameter는 전달되지 않을 수 있으며 오류 원인이 될 수 있습니다.';
            }

            let action = element.function;
            if (element.class) {
              action = element.class + element.methodType + element.function;
            }



            body.push([element.priority, action + '()', message]);
          });

          const $table = new PhpDebugBar.Widgets.TableWidget({
            data: {
              head: ['우선 순위', 'Listener', ''],
              body,
            },
          });

          $table.$el.appendTo($itemContainer);
        }
      },
    }));
})(PhpDebugBar.$);
