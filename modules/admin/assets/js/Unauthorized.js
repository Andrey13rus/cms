/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.07.2015
 */
(function(sx, $, _)
{
    sx.createNamespace('classes', sx);

    sx.classes.AppUnAuthorized = sx.classes.Component.extend({

        _init: function()
        {
            this.blocker    = new sx.classes.Blocker();
        },

        _onDomReady: function()
        {
            var $blockerLoader
            var self = this;
            this.blockerHtml = sx.block('html', {
                message: "<div style='padding: 10px;'><h2><img src='" + this.get('blockerLoader') + "'/> Загрузка...</h2></div>",
                css: {
                    "border-radius": "6px",
                    "border-width": "3px",
                    "border-color": "rgba(32, 168, 216, 0.25)",
                    "box-shadow": "0 11px 51px 9px rgba(0,0,0,.55)"
                }
            });

            this.PanelBlocker = new sx.classes.Blocker('.sx-panel', {
                message: "<div style='padding: 10px;'><h2><img src='" + this.get('blockerLoader') + "'/> Загрузка...</h2></div>",
                css: {
                    "border-radius": "6px",
                    "border-width": "1px",
                    "border-color": "rgba(32, 168, 216, 0.25)",
                    "box-shadow": "0 11px 51px 9px rgba(0,0,0,.55)"
                }
            });

         // Init CanvasBG and pass target starting location
            CanvasBG.init({
              Loc: {
                x: window.innerWidth / 2.1,
                y: window.innerHeight / 2.2
              },
            });
        },

        _onWindowReady: function()
        {
            var self = this;
            $("body").addClass('sx-styled');

            this.blockerHtml.unblock();

            _.delay(function()
            {
                $('.navbar, .sx-admin-footer').addClass('op-05').fadeIn();
            }, 1000);

            _.delay(function()
            {
                $('.sx-windowReady-fadeIn').fadeIn();
            }, 500);
        },
    });

})(sx, sx.$, sx._);