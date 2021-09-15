var jq = jQuery.noConflict();
jq(document).ready(function() {
    var numpic = jq('#slides li').size() - 1;
    var nownow = 0;
    var inout = 0;
    var TT = 0;
    var SPEED = 5000;
    jq('#slides li').eq(0).siblings('li').css({'display': 'none'});
    var ulstart = '<ul id="pagination">',
            ulcontent = '',
            ulend = '</ul>';
    ADDLI();
    var pagination = jq('#pagination li');
    pagination.eq(0).addClass('current')

    function ADDLI() {
        for (var i = 0; i <= numpic; i++) {
            ulcontent += '<li>' + '<a href="#">' + (i + 1) + '</a>' + '</li>';
        }

        jq('#slides').after(ulstart + ulcontent + ulend);
    }

    pagination.on('click', DOTCHANGE)

    function DOTCHANGE() {

        var changenow = jq(this).index();

        jq('#slides li').eq(nownow).css('z-index', '900');
        jq('#slides li').eq(changenow).css({'z-index': '800'}).show();
        pagination.eq(changenow).addClass('current').siblings('li').removeClass('current');
        jq('#slides li').eq(nownow).fadeOut(400, function() {
            jq('#slides li').eq(changenow).fadeIn(500);
        });
        nownow = changenow;
    }

    pagination.mouseenter(function() {
        inout = 1;
    })

    pagination.mouseleave(function() {
        inout = 0;
    })

    function GOGO() {

        var NN = nownow + 1;

        if (inout == 1) {
        } else {
            if (nownow < numpic) {
                jq('#slides li').eq(nownow).css('z-index', '900');
                jq('#slides li').eq(NN).css({'z-index': '800'}).show();
                pagination.eq(NN).addClass('current').siblings('li').removeClass('current');
                jq('#slides li').eq(nownow).fadeOut(400, function() {
                    jq('#slides li').eq(NN).fadeIn(500);
                });
                nownow += 1;

            } else {
                NN = 0;
                jq('#slides li').eq(nownow).css('z-index', '900');
                jq('#slides li').eq(NN).stop(true, true).css({'z-index': '800'}).show();
                jq('#slides li').eq(nownow).fadeOut(400, function() {
                    jq('#slides li').eq(0).fadeIn(500);
                });
                pagination.eq(NN).addClass('current').siblings('li').removeClass('current');

                nownow = 0;

            }
        }
        TT = setTimeout(GOGO, SPEED);
    }
    TT = setTimeout(GOGO, SPEED);
});