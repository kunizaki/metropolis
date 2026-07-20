/**
 * SPIcons plugin for rendering font icons in a dropdown
 * author JoomShaper
 */
"use strict";
(function ($, window, document, undefined) {

    /**
     * Name of the plugin
     * @type string
     */
    var name = 'spIcons';

    /**
     * The constructor of the plugin
     *
     * @param element
     * @param options
     *
     * @return void
     */
    function Plugin(element, options)
    {
        this.element = element;
        this.name = name;
        this._defaults = $.fn.spIcons.defaults;
        this.options = $.extend({}, this._defaults, options);
        //Check required options
        if (this.options.icon_name == null) {
            console.error('The icon name option is required');
            return false;
        }
        if (this.options.allow_icon_prefix && this.options.icon_prefix == null) {
            console.error('The icon prefix is required when icon prefix is allowed.');
            return false;
        }
        if (this.options.icons.length <= 0) {
            console.error('You must provide icons array. This is the icons which will be rendering');
            return false;
        }


        this.icon_copy = this.clone(this.options.icons);
        this._selected = !this.trimSelectedValue(this.options.selected_icon) ? '' : this.trimSelectedValue(this.options.selected_icon);
        this.options.styling = $.extend({}, this.options.styling, this.options.styles);
        this.searching = false;

        this.init();
    }

    /**
     * Extends the functionality within plugin prototype
     * @return plugin object
     */
    $.extend(Plugin.prototype, {
        /**
         * Init method.
         * initialization the plugin
         */
        init: function () {
            this.cacheElement();
            this.createHtmlDom();
            this.applyCustomCssDesigns();
            this.initialization(this.icon_copy);

            if (this.options.icon_view_style == 'grid') {
                this.triggerOpenGridView();
            } else if (this.options.icon_view_style == 'list') {
                this.triggerOpenListView();
            } else {
                console.error('Only grid and list view supported! Use grid or list only.');
            }
            this.triggerSearchIcon();
        },

        //Apply custom css designs.
        applyCustomCssDesigns: function () {
            this.$spicons.css({
                'width': this.options.container_width
            });
        },

        //Cache the selector element
        cacheElement: function () {
            this.$element = $(this.element);
        },

        //Clear all the selected icons
        clearSelection() {
            let self = this;
            return function () {
                self.$spicons_icon_name.empty();
                self.$spicons_icon_name.html(self.options.select_placeholder);
                self.$spicons_icon_name.addClass('hint');
                self.$spicons_input.val('');
                self.removeIconSelection();
                self._selected = '';
                self.$spicons_clear.hide();
                self.searching = false;
                self.$scroller.scrollTop(0);

                if (self.$spicons_caret.hasClass('spcions-arrow-down')) {
                    self.$spicons_caret.removeClass('spicons-arrow-down').addClass('spicons-arrow-up');
                }
            }
        },

        //Clone any array or object
        clone: function (arr) {
            return JSON.parse(JSON.stringify(arr));
        },

        /**
         * Create icon component
         * this component is used to render every
         * icon in the viewport
         * @return grid view
         */
        createIconComponent: function (icon) {
            //Creating list-icon element @parent icon-container
            let _list_icon = document.createElement('li');
            _list_icon.setAttribute('class', 'list-icon');
            _list_icon.setAttribute('title', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            _list_icon.setAttribute('data-classname', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            _list_icon.onclick = this.iconSelection($(_list_icon));
            this.$icon_container.append(_list_icon);
            this.$list_icon = $(_list_icon);


            //Creating font-wrapper element @parent list-icon
            let _font_wrapper = document.createElement('div');
            if (this._selected != '' && this._selected == icon) {
                _font_wrapper.setAttribute('class', 'font-wrapper selected-icon');
            } else {
                _font_wrapper.setAttribute('class', 'font-wrapper');
            }

            this.$list_icon.append(_font_wrapper);
            this.$font_wrapper = $(_font_wrapper);

            //Creating font-container element @parent font-wrapper
            let _font_container = document.createElement('div');
            _font_container.setAttribute('class', 'font-container');
            this.$font_wrapper.append(_font_container);
            this.$font_container = $(_font_container);

            //Creating font-cell element @parent font-cell
            let _font_cell = document.createElement('div');
            _font_cell.setAttribute('class', 'font-cell');
            this.$font_container.append(_font_cell);
            this.$font_cell = $(_font_cell);
            this.$font_cell.css({
                'font-size': this.options.styling.icon_font_size
            });

            //Creating icon-display element @parent font-cell
            let _icon_display = document.createElement('i');
            _icon_display.setAttribute('class', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            this.$font_cell.append(_icon_display);
            this.$icon_display = $(_icon_display);

            //Apply user defined styling
            this.$font_cell.css({ color: this.options.styling.icon_color });
            let self = this;

            //Hover effect on icon boxs
            this.$font_wrapper.mouseenter(function () {
                let $this = $(this);
                if (self._selected != icon) {
                    $this.find('.font-cell').css({ color: self.options.styling.icon_hover_color });
                    $this.css({ background: self.options.styling.icon_hover_background });
                }
            });

            this.$font_wrapper.mouseleave(function () {
                let $this = $(this);
                if (self._selected != icon) {
                    let cell = $this.find('.font-cell')[0];
                    cell.style.setProperty('color', self.options.styling.icon_color, 'important');
                    $this.css({ background: 'none' });
                }
            });

            //Apply selected icon's styling
            if (this.$font_wrapper.hasClass('selected-icon')) {
                this.$font_wrapper.css({
                    background: this.options.styling.selected_icon_background
                });

                this.$font_cell.css({
                    color: this.options.styling.selected_icon_color
                });
            }

        },

        /**
         * Create icon component for list view
         * @return list view
         */
        createIconComponentForListView: function (icon) {
            //Creating list-icon element @parent icon-container
            let _list_icon = document.createElement('li');
            _list_icon.setAttribute('class', 'list-icon is-list');
            _list_icon.setAttribute('title', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            _list_icon.setAttribute('data-classname', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            _list_icon.onclick = this.iconSelection($(_list_icon));
            this.$icon_container.append(_list_icon);
            this.$list_icon = $(_list_icon);


            //Creating font-wrapper element @parent list-icon
            let _font_wrapper = document.createElement('div');
            if (this._selected != '' && this._selected == icon) {
                _font_wrapper.setAttribute('class', 'font-wrapper selected-icon is-list');
            } else {
                _font_wrapper.setAttribute('class', 'font-wrapper is-list');
            }

            this.$list_icon.append(_font_wrapper);
            this.$font_wrapper = $(_font_wrapper);

            //Creating font-container element @parent font-wrapper
            let _font_container = document.createElement('div');
            _font_container.setAttribute('class', 'font-container is-list');
            this.$font_wrapper.append(_font_container);
            this.$font_container = $(_font_container);

            //Creating font-cell element @parent font-cell
            let _font_cell = document.createElement('div');
            _font_cell.setAttribute('class', 'font-cell is-list');
            this.$font_container.append(_font_cell);
            this.$font_cell = $(_font_cell);
            this.$font_cell.css({
                'font-size': this.options.styling.icon_font_size
            });

            //Creating icon-display element @parent font-cell
            let _icon_display = document.createElement('i');
            _icon_display.setAttribute('class', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + icon : icon);
            this.$font_cell.append(_icon_display);
            this.$icon_display = $(_icon_display);

            //Apply user defined styling
            this.$font_cell.css({ color: this.options.styling.icon_color });
            let self = this;

            //Creating icon info panel @parent list-icon
            let _icon_info = document.createElement('div');
            _icon_info.setAttribute('class', 'icon-info is-list');
            this.$list_icon.append(_icon_info);

            this.$font_wrapper.css({
                display: 'inline-block'
            });

            this.$icon_info = $(_icon_info);
            this.$icon_info.text(icon);


            //Hover effect on icon boxs
            this.$list_icon.mouseenter(function () {
                let $this = $(this);
                if (self._selected != icon) {
                    $this.find('.font-cell').css({ color: self.options.styling.icon_hover_color });
                    $this.find('.font-wrapper').css({ background: self.options.styling.icon_hover_background });
                    $this.css({
                        background: '#F5F5F5'
                    });
                }
            });

            this.$list_icon.mouseleave(function () {
                let $this = $(this);
                if (self._selected != icon) {
                    let cell = $this.find('.font-cell')[0];
                    cell.style.setProperty('color', self.options.styling.icon_color, 'important');
                    $this.find('.font-wrapper').css({ background: 'none' });
                    $this.css({
                        background: '#fff'
                    });
                }
            });

            //Apply selected icon's styling
            if (this.$font_wrapper.hasClass('selected-icon')) {
                this.$font_wrapper.css({
                    background: this.options.styling.selected_icon_background
                });

                this.$font_cell.css({
                    color: this.options.styling.selected_icon_color
                });
            }

        },

        /**
         * Create the wireframe of the plugin
         * including the plugin body
         * and design structures.
         */
        createHtmlDom: function () {
            //Creating spicons element @parent this.$element
            let _spicons = document.createElement("div");
            _spicons.setAttribute('class', 'spicons');
            this.$spicons = $(_spicons);

            //Creating spicons-wrapper element @parent spicons
            let _spicons_wrapper = document.createElement("div");
            _spicons_wrapper.setAttribute('class', 'spicons-wrapper');
            this.$spicons.append(_spicons_wrapper);
            this.$spicons_wrapper = $(_spicons_wrapper);

            //Creating spicons-header element @parent spicons-wrapper
            let _spicons_header = document.createElement("div");
            _spicons_header.setAttribute('class', 'spicons-header');
            this.$spicons_wrapper.append(_spicons_header);
            this.$spicons_header = $(_spicons_header);

            //Creating spicons-icon-container @parent spicons-header
            let _spicons_icon_container = document.createElement('div');
            _spicons_icon_container.setAttribute('class', 'spicons-icon-contaner');
            this.$spicons_header.append(_spicons_icon_container);
            this.$spicons_icon_container = $(_spicons_icon_container);

            //Creating spicons-icon + spicons-icon-name + hint @parent spicons-icon-container
            let _spicons_icon = document.createElement('span');
            _spicons_icon.setAttribute('class', 'spicons-icon');

            let _spicons_icon_name = document.createElement('span');
            if (this._selected != '') {
                _spicons_icon_name.setAttribute('class', 'spicons-icon-name');
                let _i = document.createElement('i');
                _i.setAttribute('class', this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + this._selected : this._selected);
                _spicons_icon_name.append(_i);
                _i.setAttribute('style', 'color: ' + this.options.styling.header_icon_color);
                _spicons_icon_name.innerHTML += ' ' + (this.options.allow_icon_prefix ? this.options.icon_prefix + ' ' + this._selected : this._selected);
            } else {
                _spicons_icon_name.setAttribute('class', 'spicons-icon-name hint');
                _spicons_icon_name.innerHTML = this.options.select_placeholder;
            }

            let _spicons_input = document.createElement('input');
            _spicons_input.type = 'hidden';
            _spicons_input.name = this.options.input_name;
            _spicons_input.setAttribute('class', 'spicons-input');

            //Check if a selected item has given
            if (this._selected != '') {
                let full_icon_name = '';
                if (this.options.allow_icon_prefix) {
                    full_icon_name = this.options.icon_prefix + ' ' + this._selected;
                } else {
                    full_icon_name = this._selected;
                }
                _spicons_input.setAttribute('value', full_icon_name);
            } else {
                _spicons_input.setAttribute('value', '');
            }
            this.$spicons_header.append(_spicons_input);
            this.$spicons_input = $(_spicons_input);

            this.$spicons_icon_container.append(_spicons_icon);
            this.$spicons_icon_container.append(_spicons_icon_name);
            this.$spicons_icon = $(_spicons_icon);
            this.$spicons_icon_name = $(_spicons_icon_name);

            //Creating pull-right element @parent spicons-icon-container
            let _pull_right = document.createElement('span');
            _pull_right.setAttribute('class', 'pull-right');
            this.$spicons_icon_container.append(_pull_right);
            this.$pull_right = $(_pull_right);

            //Creating spicons-clear + spicons-caret elements @parent pull-right
            let _spicons_clear = document.createElement('span');
            _spicons_clear.setAttribute('class', 'spicons-clear');
            _spicons_clear.innerHTML = 'X';
            _spicons_clear.onclick = this.clearSelection();

            let _spicons_caret = document.createElement('span');
            _spicons_caret.setAttribute('class', 'spicons-arrow-down spicons-caret');

            this.$pull_right.append(_spicons_clear);
            this.$pull_right.append(_spicons_caret);

            this.$spicons_clear = $(_spicons_clear);
            this.$spicons_caret = $(_spicons_caret);
            this._selected != '' ? this.$spicons_clear.show() : this.$spicons_clear.hide();

            //Creating spicons-body element @parent spicons-wrapper
            let _spicons_body = document.createElement('div');
            _spicons_body.setAttribute('class', 'spicons-body');
            _spicons_body.setAttribute('style', 'display: none;');
            this.$spicons_wrapper.append(_spicons_body);
            this.$spicons_body = $(_spicons_body);

            //Creating search input element @parent spicons-body
            let _search = document.createElement('input');
            _search.setAttribute('class', 'search');
            _search.setAttribute('placeholder', this.options.search_placeholder);
            this.$spicons_body.append(_search);
            this.$search = $(_search);

            //Creating scroller element @parent spicons-body
            let _scroller = document.createElement('div');
            _scroller.setAttribute('class', 'scroller');
            this.$spicons_body.append(_scroller);
            this.$scroller = $(_scroller);

            //Creating icon-container element @parent scroller
            let _icon_container = document.createElement('ul');
            if (this.options.icon_view_style == 'list') {
                _icon_container.setAttribute('class', 'icon-container is-list');
                $(_icon_container).css({
                    width: parseInt(this.options.container_width) - 50 + 'px'
                })
            } else {
                _icon_container.setAttribute('class', 'icon-container');
            }
            this.$scroller.append(_icon_container);
            this.$icon_container = $(_icon_container);

            this.$element.append(this.$spicons);
        },

        //Select icon
        iconSelection(element) {
            let self = this;
            return function () {
                self.$spicons_icon_name.html('');
                let _icon = document.createElement('i');
                _icon.setAttribute('class', element.data('classname'));
                _icon.setAttribute('style', 'color: ' + self.options.styling.header_icon_color);
                self.$spicons_icon_name.append(_icon);
                self.$spicons_icon_name.html(self.$spicons_icon_name.html() + ' ' + element.data('classname'));
                self.$spicons_input.val(element.data('classname'));
                self.$spicons_icon_name.removeClass('hint');
                self.$spicons_body.hide();
                self.$spicons_clear.show();

                //Remove previous selection
                self.removeIconSelection();
                self._selected = '';
                self._selected = self.trimSelectedValue(element.data('classname'));
                if (self.$spicons_caret.hasClass('spicons-arrow-up')) {
                    self.$spicons_caret.removeClass('spicons-arrow-up').addClass('spicons-arrow-down');
                }
            }
        },

        //Initialize icon rendering
        initialization: function (icons) {
            let iconSplice = icons.splice(0, this.options.icon_limit);
            this.renderIcons(iconSplice);
            if (icons.length > 0) {
                this.scrollingAndLoading(icons);
            }
        },

        //Load more data @return mixed
        loadMore: function (input, limit) {
            let result = [];
            if (input.length > 0) {
                result = input.splice(0, limit);
                return result;
            }
            return false;
        },

        //Reload the icon container
        reloadIconContainer: function () {
            this.$icon_container.empty();
        },

        //Remove previously selected icon @return void
        removeIconSelection: function () {
            $('.selected-icon').removeClass('selected-icon');
        },

        //Render the icons
        renderIcons: function (arr) {
            var self = this;
            arr.forEach(function (value) {
                if (self.options.icon_view_style == 'list') {
                    self.$icon_container.append(self.createIconComponentForListView(value));
                } else {
                    self.$icon_container.append(self.createIconComponent(value));
                }
            });
        },

        //Scrolling and loading icons
        scrollingAndLoading: function (icons) {
            let self = this;
            this.$scroller.on('scroll', function () {
                let $this = $(this);
                let scrollTop = $this.scrollTop();
                let innerHeight = $this.innerHeight();
                let scrollHeight = $this[0].scrollHeight;

                if (scrollTop + innerHeight >= scrollHeight) {
                    let splice = self.loadMore(icons, self.options.icon_limit);
                    if (splice !== false) {
                        self.renderIcons(splice);
                    } else {
                        return false;
                    }
                }
            });
        },

        //Search icon
        searchIcon: function (arr, item) {
            let result = [];
            let size = arr.length;
            for (let i = 0; i < size; i++) {
                if (arr[i].indexOf(item) !== -1) {
                    result.push(arr[i]);
                }
            }
            return result;
        },

        //Event handlers
        //Trigger to open grid view
        triggerOpenGridView: function () {
            var self = this;
            this.$spicons_header.on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                //Reset search
                self.searching = false;
                self.$scroller.scrollTop(0);

                //If restart enabled then restart the container
                if (self.options.restart && !self.searching) {
                    self.$search.val('');
                    let _icons = self.clone(self.options.icons);
                    self.reloadIconContainer();
                    self.initialization(_icons);
                }
                self.$spicons_body.toggle();
                self.$spicons_caret.toggleClass('spicons-arrow-down spicons-arrow-up');
                self.$search.focus();
            });
        },

        //Trigger to open list view.
        triggerOpenListView: function () {
            var self = this;
            this.$spicons_header.on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                //Reset search
                self.searching = false;
                self.$scroller.scrollTop(0);

                //If restart enabled then restart the container
                if (self.options.restart && !self.searching) {
                    self.$search.val('');
                    let _icons = self.clone(self.options.icons);
                    self.reloadIconContainer();
                    self.initialization(_icons);
                }
                self.$spicons_body.toggle();
                self.$spicons_caret.toggleClass('spicons-arrow-down spicons-arrow-up');
                self.$search.focus();
            });
        },

        //Trigger to search icon
        triggerSearchIcon: function () {
            let self = this;
            this.$search.on('keyup', function (event) {
                event.preventDefault();
                self.$scroller.off('scroll');
                let $this = $(this);
                setTimeout(function () {
                    self.reloadIconContainer();
                    let value = $this.val();
                    self.$scroller.scrollTop(0);
                    if (value == '') {
                        let initIcons = self.clone(self.options.icons);
                        self.initialization(initIcons);
                        self.searching = false;
                    } else {
                        let search = [];
                        self.searching = true;
                        search = self.clone(self.searchIcon(self.options.icons, value));
                        self.initialization(search);
                    }
                }, 500);
            });
        },
        //Trim and format icon name. @return only icon name triming prefix.
        trimSelectedValue: function (value) {
            if (typeof(value) != 'undefined' && value != '') {
                let _value = value.split(' ');
                if (_value.length > 1) {
                    return _value[1];
                } else {
                    return value;
                }
            } else {
                return false;
            }
        }
    });

    //Call the plugin instance. Main plugin function.
    $.fn.spIcons = function (options) {
        this.each(function () {
            if (!$.data(this, name)) {
                $.data(this, name, new Plugin(this, options));
            }
        });
        return this;
    }

    //Default options values.
    $.fn.spIcons.defaults = {
        icon_name: null,
        icon_prefix: null,
        icon_view_style: 'grid',
        select_placeholder: 'Select icon...',
        search_placeholder: 'Search',
        input_name: 'spicons_input',
        allow_icon_prefix: true,
        container_width: '400px',
        selected_icon: '',
        restart: true,
        icon_limit: 100,
        styles: {},
        styling: {
            icon_font_size: 'initial',
            icon_color: '#f08080',
            icon_hover_background: '#92278F',
            icon_hover_color: '#FFF',
            selected_icon_color: '#FFF',
            selected_icon_background: '#92278F',
            header_icon_color: '#000'
        },
        icons: []
    }
})(jQuery, window, document);