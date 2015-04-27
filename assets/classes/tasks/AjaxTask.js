/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.04.2015
 */
(function(sx, $, _)
{
    sx.createNamespace('classes.tasks', sx);

    /**
     * Задача которую необходимо выполнить.
     */
    sx.classes.tasks.AjaxTask = sx.classes.tasks._Task.extend({

        construct: function (ajaxQuery, opts)
        {
            var self = this;
            if (!ajaxQuery instanceof sx.classes._AjaxQuery)
            {
                throw new Error('Передан неконнектный ajaxQuery объект');
            }

            opts = opts || {};
            opts.ajaxQuery = ajaxQuery;

            this.applyParentMethod(sx.classes.Component, 'construct', [opts]); // TODO: make a workaround for magic parent calling
        },

        _init: function()
        {
            this._initQuery();
        },

        _initQuery: function()
        {
            var self = this;

            this.get("ajaxQuery").onComplete(function(e, data)
            {
                self.trigger("complete", {
                    'task'      : self,
                    'result'    : data
                });
            });

            return this;
        },

        execute: function()
        {
            var self = this;

            this.trigger("beforeExecute", {
                'task' : this
            });

            this.get("ajaxQuery").execute();
        }
    });

})(sx, sx.$, sx._);