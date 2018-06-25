/**
 * @file form.js
 *
 * Javascript functions to make the UI a bit nicer.
 */

(function ($, window) {

    var hostname = window.location.hostname.replace('www.', '');

    /**
     * Set up a click handler to show a confirmation dialog and return the result.
     *
     * Expects a data-confirm attribute on the element.
     *
     * @returns boolean
     */
    function confirm() {
        var $this = $(this);
        $this.click(function () {
            return window.confirm($this.data('confirm'));
        });
    }

    /**
     * Before the user leaves a URL check if there are dirty forms and confirm leaving.
     *
     * @param event e
     * @returns string
     */
    function windowBeforeUnload(e) {
        var clean = true;
        $('form').each(function () {
            var $form = $(this);
            if ($form.data('dirty')) {
                clean = false;
            }
        });
        if (!clean) {
            var message = 'You have unsaved changes.';
            e.returnValue = message;
            return message;
        }
    }

    /**
     * When a user changes a form this function marks the form as dirty.
     */
    function formDirty() {
        var $form = $(this);
        $form.data('dirty', false);
        $form.on('change', function () {
            $form.data('dirty', true);
        });
        $form.on('submit', function () {
            $(window).unbind('beforeunload');
        });
    }

    /**
     * Open a form button in a popup window.
     *
     * @param event e
     */
    function formPopup(e) {
        e.preventDefault();
        var url = $(this).prop('href');
        window.open(url, "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=60,left=60,width=500,height=600");
    }

    /**
     * Apply the select2entity javascript goodness to a text input.
     */
    function simpleCollection() {
        $('.collection-simple').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: false,
            after_add: function (collection, element) {
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-container').css('width', '100%');
                return true;
            },
        });
    }

    /**
     * Apply the select2entity javascript goodness to a collection of inputs.
     */
    function complexCollection() {
        $('.collection-complex').collection({
            allow_up: false,
            allow_down: false,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: true,
            after_add: function (collection, element) {
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-container').css('width', '100%');
                return true;
            },
        });
    }

    /**
     * Make off-site links open in a new tab.
     */
    function link() {
        if (this.hostname.replace('www.', '') === hostname) {
            return;
        }
        $(this).attr('target', '_blank');
    }

    /**
     * Do some nice things with the form upload controls.
     */
    function uploadControls() {
        var $input = $(this);
        $input.change(function () {
            if ($input.data('maxsize') && $input.data('maxsize') < this.files[0].size) {
                alert('The selected file is too big.');
            }
            $('#filename').val($input.val().replace(/.*\\/, ''));
        });
    }

    /**
     * Get the UI initialized for each page load.
     */
    $(document).ready(function () {
        $(window).bind('beforeunload', windowBeforeUnload);
        $('form').each(formDirty);
        $('input:file').each(uploadControls);
        $("*[data-confirm]").each(confirm);
        $("a.popup").click(formPopup);
        $("a").each(link);
        if (typeof $().collection === 'function') {
            simpleCollection();
            complexCollection();
        }
    });

})(jQuery, window);
