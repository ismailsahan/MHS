(function($) {

    'use strict';

    $.scroller = function(elm, options) {
        return new Scroller(elm, options);
    };

    $.fn.scroller = function(options) {
        return this.size() == 1 ? $.scroller(this, options) : this.each(function() {
            $.scroller(this, options);
        });
    };

    $.fn.scroller.defaults = {
        delay: 4000, // 停驻时间
        speed: 500, // 过渡时间
        itemunit: 0, // 每行项目数
        visiblerownum: 0, // 可见的行数
        step: 1, // 单次滚动行数
        fillitems: true, // 填充列表
        easing: "swing",
        itemSelector: "li", // 列表项选择器
        mouseover: function() {},
        mouseout: function() {},
    };

    var scrollers = [];

    function Scroller(elm, options) {
        this.$elm = $(elm);
        this.idx = this.$elm.data("scroller");
        if (this.idx != undefined && this.idx > -1 && scrollers[this.idx] != null) return scrollers[this.idx];
        this.opts = this.chkopt(elm, options);
        this.init();
        return this;
    }

    Scroller.prototype = {
        init: function() {
            this.stop = 0;
            this.forcestop = 0;
            this.rows = [];

            this.items = this.$elm.find(this.opts.itemSelector);
            this.itemnum = this.items.length;
            this.itemcontainer = this.items.parent();
            if (this.items.length * this.opts.step <= this.opts.visiblerownum) return;
            if (this.opts.fillitems) this.fillitems();
            this.$elm.find(this.opts.itemSelector).slice(0, this.opts.visiblerownum * this.opts.itemunit).clone(true).appendTo(this.itemcontainer);

            this.items = this.$elm.find(this.opts.itemSelector);
            this.rownum = 1;
            this.rowcount = this.items.size() / this.opts.itemunit;

            for (var i = 0; i < this.rowcount - this.opts.visiblerownum + 1; i += this.opts.step)
                this.rows.push(this.items[this.opts.itemunit * i].offsetTop - this.items[0].offsetTop);

            delete this.items;
            delete this.rowcount;
            delete this.itemcontainer;
            this.lastrow = this.rows.length - 1;

            var idx = scrollers.length;
            this.idx = idx;
            this.$elm.data("scroller", this.idx);
            delete this.init;
            delete this.chkopt;
            delete this.fillitems;
            scrollers.push(this);
            this.interval = setInterval(function() {
                var $this = scrollers[idx];
                if ($this.stop || $this.forcestop) return;
                $this.$elm.animate({
                    scrollTop: $this.rows[$this.rownum] + "px"
                }, $this.opts.speed, $this.opts.easing, function() {
                    if ($this.rownum >= $this.lastrow) {
                        $this.$elm.scrollTop(0);
                        scrollers[idx].rownum = 1;
                        return;
                    }
                    scrollers[idx].rownum++;
                });
            }, this.opts.delay);

            this.$elm.on("mouseover", function() {
                scrollers[idx].stop = 1;
                scrollers[idx].opts.mouseover.call(this);
            }).on("mouseout", function() {
                scrollers[idx].stop = 0;
                scrollers[idx].opts.mouseout.call(this);
            });

            //scrollers[this.idx].itemScroll(this.idx);
            //this.itemScroll();
            //this.itemScroll = $.scroller.prototype.itemScroll;
            //$.scroller.prototype.itemScroll.call(this);
        },
        chkopt: function(elm, options) {
            options = $.extend({}, $.fn.scroller.defaults, options);

            if (!options.itemunit) {
                var offsetTop;
                $(elm).find(options.itemSelector).each(function() {
                    if (offsetTop && $(this).offset().top > offsetTop) return;
                    offsetTop = $(this).offset().top;
                    options.itemunit++;
                });
            }

            if (!options.visiblerownum) {
                var containerHeight = $(elm).height(),
                    itemHeight = $(elm).find(options.itemSelector).outerHeight(true);
                if ($(elm).parent().height() < containerHeight) containerHeight = $(elm).parent().height();
                options.visiblerownum = Math.round(containerHeight / itemHeight);
            }

            return options;
        },
        fillitems: function() {
            while (this.$elm.find(this.opts.itemSelector).size() % this.opts.itemunit != 0) {
                this.items.clone(true).appendTo(this.itemcontainer);
            }
            if (this.opts.step > 1) {
                this.items = this.$elm.find(this.opts.itemSelector);
                while ((this.$elm.find(this.opts.itemSelector).size() / this.opts.itemunit) % this.opts.step != 0) {
                    this.items.clone(true).appendTo(this.itemcontainer);
                }
            }
        },
        stop: function() {
            scrollers[this.idx].forcestop = 1;
        },
        start: function() {
            scrollers[this.idx].forcestop = 0;
        },
        setOption: function(options) {
            scrollers[this.idx].opts = $.extend({}, this.opts, options);
        },
        destroy: function() {
            clearInterval(scrollers[this.idx].interval);
            scrollers[this.idx] = null;
            var lastitemidx = this.itemnum,
                itemSelector = this.opts.itemSelector;
            this.$elm.off("mouseover").off("mouseout");
            this.$elm.animate({
                scrollTop: "0px"
            }, this.opts.speed, this.opts.easing, function() {
                $(this).data("scroller", -1);
                $(this).find(itemSelector).slice(lastitemidx).remove();
            });
        }
    };

}(jQuery));