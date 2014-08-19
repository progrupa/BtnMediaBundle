(function(app, $, undefined){

    // Add events
    var addEvents = function(context) {
        app.tools.getOnce('btn-media', context).each(function() {
            var element = $(this);
            element.btnMediaModal();
        });
    };

    app.init(function(msg, data) {
        addEvents(data.context);
    });

    app.refresh(function(msg, data) {
        addEvents(data.context);
    });

})(BtnApp, jQuery);
