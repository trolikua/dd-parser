
// parser
$(function () {

    var list_cat = [];
    var cur_i_cat = 0;
    var offset = 0;
    var max_offset = 0;

    // start
    $('#startParse').click(function () {
        if ($(this).attr('disable')) {
            return;
        }
        $(this).attr('disable', true);

        $.ajax({
            method: 'POST',
            url: 'parser.php',
            data: {
                action: 'start'
            },
            success: function (res) {
                res = JSON.parse(res);

                list_cat = res.list_cat;
                console.log(res);

                $('#cont').append('<div>Найдено каталогов: <b>'+ list_cat.length +'</b></div>');
                if (list_cat.length != 0) {
                    console.log(list_cat[cur_i_cat]);
                    parseData(list_cat[cur_i_cat]);
                }
            }
        });

    });

    function parseData () {

        $.ajax({
            method: 'POST',
            url: 'parser.php',
            data: {
                action: 'parse',
                id_cat: list_cat[cur_i_cat],
                offset: offset,
                max_offset: max_offset
            },
            success: function (res) {
                res = JSON.parse(res);

                console.log(res);
                offset = res.offset;
                max_offset = res.max_offset;
                if (offset > max_offset) {
                    offset = 0;
                    max_offset = 0;

                    $('#cont').append('<div>Каталог "'+ list_cat[cur_i_cat] +'" завершен.</div>');

                    var new_i = cur_i_cat + 1;
                    if (new_i >= list_cat.length) {
                        console.log('success parse');
                        $('#cont').append('<div>Создание Excel файла...</div>');
                        saveExcel();
                        return;
                    }
                    cur_i_cat = new_i;
                }

                parseData();
            }
        });

    }

    function saveExcel () {
        $.ajax({
            method: 'POST',
            url: 'save_excel.php',
            //dataType: 'json',
            data: {

            },
            success: function (res) {
                //res = JSON.parse(res);
                //console.log(res);
                $('#cont').append('<a href="price.xlsx"><button>Скачать Excel файл</button></a>');
            },
            error: function(status, exception) {
                console.log('Exception:', exception);
                console.log('status: ', status);
            }
        });
    }

});