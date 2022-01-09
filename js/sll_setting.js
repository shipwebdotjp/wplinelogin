
jQuery(function ($) {
    $("#stabs").tabs({ active: sll_json['active_tab'] });
    $(".sll-color-picker").each(
        function (index) {
            $(this).wpColorPicker({ defaultColor: $(this).attr("data-default-color") });
        }
    );
    $(document).ready(function () {
        $(".sll-multi-select").multiselect({
            selectedList: 5,
            linkInfo: {
                checkAll: { text: 'すべて選択', title: 'すべて選択' },
                uncheckAll: { text: '選択解除', title: '選択解除' }
            },
            noneSelectedText: "未選択",
            selectedText: "# 個選択"
        });

    });
    $(".wrap").tooltip();
});