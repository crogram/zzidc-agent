    var _curDocWidth = 0;
    /**
     * @method 卡片布局函数
     * @returns {number}
     */
    var layoutCard = function(itemCount) {
        var docWidth = document.documentElement.clientWidth;
        _curDocWidth = docWidth;
        // 卡片宽度
        var cardWidth = docWidth < 470 ? 252 : 332;

        // 下一个卡片嵌入屏幕的宽度
        var bulb = docWidth < 470 ? 15 : 30;

        // 计算卡片两边的margin
        var cardMargin = (docWidth / 2 - cardWidth / 2 - bulb) / 2;

        // 计算包围卡片的div的margin
        var outerMargin = bulb + cardMargin;

        // 计算两个卡片的距离
        var step = cardWidth + 2 * cardMargin;

        // 计算包围卡片div的宽度
        var outerWidth = itemCount * (cardWidth + 2 * cardMargin);

        $('.pd-set').css('width', outerWidth + 'px').css('margin', '0 ' + outerMargin + 'px');
        $('.pd-card').css('margin', '0 ' + cardMargin + 'px');
        return step;
    }

    var layoutTitle = function(itemCount) {
        var docWidth = document.documentElement.clientWidth;
        var docHeight = document.documentElement.clientHeight;
        // title宽度
        var titleWidth = docWidth < 470 ? 55 : 61;
        var titleMargin = 30;
        var outerMargin = docWidth / 2 - titleWidth / 2 - titleMargin;
        var outerWidth = itemCount * (titleWidth + 2 * titleMargin);
        $('.type-inner').css('width', outerWidth + 'px').css('margin', '0 ' + outerMargin + 'px');
        $('.type-title').css('margin', '0 ' + titleMargin + 'px');

        var mheight = $('.middle_helper').offset().height;
        $('.type').css('top', (46 + mheight - 160 - 40 - (docHeight > 500 ? 20 : 10)) + 'px');

        var step = titleWidth + 2 * titleMargin;
        return step;
    }



    function Shop() {

    }

    Shop.prototype.init = function() {
        var cardStep = layoutCard(ITEM_COUNT);
        var titleStep = layoutTitle(ITEM_COUNT);
        this.cardSlider = new Slider($('.pd-set')[0], cardStep, ITEM_COUNT);
        this.titleSlider = new Slider($('.type-inner')[0], titleStep, ITEM_COUNT, { disableTouch: true });
        this.cardSlider.link(this.titleSlider);

        this.cardSlider.on('locate', function(itemIndex) {
            //util.store.set('shop_cvm_curIndex', itemIndex);
        });
        var titles = $('.type-title'), selectClass = 'type-title-select', curIndex = 0;
        this.titleSlider.on('locate', function(itemIndex) {
            if(itemIndex == curIndex) return;
            titles.eq(curIndex).removeClass(selectClass);
            titles.eq(itemIndex).addClass(selectClass);
            curIndex = itemIndex;
        });

        // 0-跳转；1-刷新；2-历史记录回退
        // ios7及以下 不支持performance
        if(typeof performance == 'undefined' || (performance.navigation && performance.navigation.type)) {
           // this.cardSlider.locate(util.store.get('shop_cvm_curIndex') || 0);
        }

        var goodsInfo = [];
        $('.pd-card').each(function() {
            goodsInfo.push({ 'goodsKey': $(this).attr('key') });
        });
        /*
        util.ajaxRequest({
            url: "/shop/queryPrice",
            data: {
                goodsInfo: goodsInfo
            },
            sucCb: function(ret) {
                var data = ret.data;
                if(!data) return;
                for(var key in data) {
                    var cost = data[key].realCostMonth;
                    cost = cost ? parseFloat(cost) / 100 : '--';
                    $('.price_holder', '.pd-card[key=' + key + ']').html(cost);
                }
            },
            errCb: function(ret) {
                util.errorTip.show('获取价格失败:' + ret.msg);
            }
        });
	*/
        $('#loading').hide();
        $('.content').show();
        this._event();
    }

    Shop.prototype._event = function() {
        var me = this;
        $(window).on('resize', function() {
            me.cardSlider.updateStep(layoutCard(ITEM_COUNT));
            me.titleSlider.updateStep(layoutTitle(ITEM_COUNT));
        });
        $('.pd-card').click(function() {
            $('.submit_btn', this).trigger('click');
        });
        $('.type-title').click(function() {
            var itemIndex = $(this).attr('index');
            itemIndex && me.cardSlider.locate(itemIndex);
        });
        $('.submit_btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(this);
            var key = $this.attr('key');
            if(key && !$this.hasClass('ct-op-disable')) {
                //location.href = '#' + key;
                location.href = '#';
            }
        });

        $(window).on('load', function() {
            window.scrollTo(0,1);
        });
    }