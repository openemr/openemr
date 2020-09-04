/**
 * View logic for the application.  Defines generic views for collections and
 * models as well as helper methods for template generation
 *
 * Derived from phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 *
 */
// eslint-disable-next-line no-var
var view = {

    version: 1.1,

    /**
     * Given an object with properties totalPages and currentPage, return
     * HTML page navigator
     */
    getPaginationHtml(page) {
        let html = '';
        if (page.totalPages > 1) {
            html += '<div class="pagination"><ul>';

            let first = 1;
            let last = (1 * page.totalPages);

            if (last > 10) {
                first = (1 * page.currentPage) - 5;
                first = first > 1 ? first : 1;

                last = last > (first + 9) ? (first + 9) : last;
            }

            if (first > 1) {
                html += `<li><a class="pageButton" id="page-${first - 1}" href="#">&laquo;</a></li>`;
            }

            for (let i = first; i <= last; i += 1) {
                html += `
                    <li${page.currentPage === i ? ' class="active"' : ''}>
                        <a class="pageButton" id="page-${i}" href="#">${i}</a>
                    </li$>`;
            }

            if (last < (1 * page.totalPages)) {
                html += `<li><a class="pageButton" id="page-${last + 1}" href="#">&raquo;</a></li>`;
            }
            html += '</ul></div>';
        }
        return html;
    },

    /**
     * CollectionView implements a generic view for rendering collections
     * Note that the property templateEl must be set after instantiation
     */
    CollectionView: Backbone.View.extend({

        /** @var compiled underscore template */
        template: null,

        /** @var templateEl (required) is the element containing the underscore template code */
        templateEl: null,

        /** @var automatically update the model on every change  */
        automaticallyUpdateModel: false,

        /** @var override backbone.events - handle when the view has been changed */
        events: {
            change: 'handleViewChange',
        },

        /** initialize is fired by backbone immediately after construction */
        initialize(options) {
            // if the collection changes these will fire
            this.listenTo(this.collection, 'add', this.handleCollectionAdd);
            this.listenTo(this.collection, 'remove', this.handleCollectionRemove);
            this.listenTo(this.collection, 'reset', this.handleCollectionReset);
            this.listenTo(this.collection, 'sync', this.handleCollectionSync);

            // if a model inside the collection changes this will fire
            this.listenTo(this.collection, 'change', this.handleModelChange);

            // allow the custom options to be initialized at construction
            this.templateEl = options.templateEl;
            if (options.automaticallyUpdateModel) {
                this.automaticallyUpdateModel = options.automaticallyUpdateModel;
            }

            if (options.on) {
                for (const evt in options.on) {
                    if (Object.prototype.hasOwnProperty.call(options.on, evt)) {
                        this.on(evt, options.on[evt]);
                    }
                }
            }
        },

        /** prepare will pre-compile the underscore template if necessary */
        prepare() {
            if (!this.template) {
                const tpl = this.templateEl.html();
                this.template = _.template(tpl);
            }
        },

        /** render populates the container element with contents of the template */
        render() {
            this.prepare();

            // convert the collection to a simple json object so the templates are not so verbose
            const html = this.template({
                items: this.collection,
                page: {
                    totalResults: this.collection.totalResults,
                    totalPages: this.collection.totalPages,
                    currentPage: this.collection.currentPage,
                    orderBy: this.collection.orderBy,
                    orderDesc: this.collection.orderDesc,
                },
            });

            // if this.el is null then it's likely that the collection view
            // was initialized prior to document.ready firing
            if (typeof (this.el) === 'undefined' && console) {
                console.warn('CollectionView.render element is not defined.  Collection may not render properly.');
            }

            $(this.el).html(html);

            // let any interested parties know that render is complete
            this.trigger('rendered');
        },

        /** whenever a model is added to the collection */
        handleCollectionAdd(m, r, ev) {
            // if the collection is parsing, then wait for the sync event to re-render
            if (!ev.parse) {
                this.render();
            }
        },

        /** if collection changes re-render */
        handleCollectionRemove(m, r, ev) {
            // if the collection is parsing, then wait for the sync event to re-render
            if (!ev.parse) {
                this.render();
            }
        },

        /** if collection changes re-render */
        handleCollectionReset(ev) {
            this.render();
        },

        /** whenever the collection has been synced with data from the server */
        handleCollectionSync(obj, resp, req) {
            // this gets fired when the collection is synced with the server, as well as
            // when any model within the collection is synced.  However we only want
            // to re-render if this is a collection sync.
            if (obj instanceof Backbone.Collection) {
                this.render();
            }
        },

        /** if collection changes re-render */
        handleModelChange(ev) {
            this.render();
        },

        /**
         * fires when the view has changed (normally via user input).  When the user
         * updates the value of a form input within the view, this will fire.
         *
         * If automaticallyUpdateModel=true then model changes will be posted to the
         * server automatically
         *
         * In order for this method to determine the primary key and property name
         * of the input that was updated the id property of the input must be set
         * in the following format:
         *
         * <input id="[prop]_[id]"  ... />
         *
         * where [prop] is the name of the model propery and [id] is the
         * id (unique id) of the model
         */
        handleViewChange(ev) {
            if (this.automaticallyUpdateModel) {
                // use the name of the input element to determine what field changed
                const pair = ev.target.id.split('_');
                const propName = pair[0];
                const id = pair[1];

                //  get the new value
                const val = $(ev.target).val();

                // get the model from the collection
                const m = this.collection.get(id);

                // specify the property and new value
                const options = {};
                options[propName] = val;

                // post model change to server (which will fire a change event on the model)
                m.set(options);
            }
        },
    }),

    /**
     * ModelView implements a generic view for displaying an individual model
     * as an editable form.
     * Note that the property templateEl must be set after instantiation
     */
    ModelView: Backbone.View.extend({

        /** @var compiled underscore template */
        template: null,

        /** @var templateEl (required) is the element containing the underscore template code */
        templateEl: null,

        /** @var automatically update the model on every change (recommended value is false)  */
        automaticallyUpdateModel: false,

        /** @var override backbone.events - handle when the view has been changed */
        events: {
            change: 'handleViewChange',
        },

        /** initialize is fired by backbone */
        initialize(options) {
            // if a model inside the collection changes this will fire
            if (this.model) this.listenTo(this.model, 'change', this.handleModelChange);

            // allow the custom options to be initialized at construction
            if (options.templateEl) {
                this.templateEl = options.templateEl;
            }
            if (options.automaticallyUpdateModel) {
                this.automaticallyUpdateModel = options.automaticallyUpdateModel;
            }

            if (options.on) {
                for (const evt in options.on) {
                    if (Object.prototype.hasOwnProperty.call(options.on, evt)) {
                        this.on(evt, options.on[evt]);
                    }
                }
            }
        },
        /** prepare will pre-compile the underscore template if necessary */
        prepare() {
            if (!this.template) {
                const tpl = this.templateEl.html();
                this.template = _.template(tpl);
            }
        },

        /** render populates the container element with contents of the template */
        render() {
            this.prepare();

            const html = this.template({
                item: this.model,
            });

            // if this.el is null then it's likely that the collection view
            // was initialized prior to document.ready firing
            if (typeof (this.el) === 'undefined' && console) console.warn('ModelView.render element is not defined.  Model may not render properly.');

            $(this.el).html(html);

            // let any interested parties know that render is complete
            this.trigger('rendered');
        },

        /** if model changes re-render */
        handleModelChange(ev) {
            this.render();
        },

        /**
         * fires when the view has changed (normally via user input).  When the user
         * updates the value of a form input within the view, this will fire.
         *
         * If automaticallyUpdateModel=true then model changes will be posted to the
         * server automatically
         *
         * Implementing this can put a lot of load on the server because an update
         * will be sent on every field change.  It is likely preferable to
         * wait until a "save" button is clicked instead and save the changes all
         * at once.
         */
        handleViewChange(ev) {
            if (this.automaticallyUpdateModel) {
                const name = window.event.target.name;
                const newValue = $(window.event.target).val();

                const options = {};
                options[name] = newValue;

                // post model change to server (which will fire a change event on the model)
                model.set(options);
            }
        },
    }),
};
