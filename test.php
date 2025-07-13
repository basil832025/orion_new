<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Select2 з чекбоксами (столи)</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        .checkbox { display: flex; align-items: center; }
        .checkbox input { margin-right: 8px; }
        .select2-results__option { padding: 0 !important; }
    </style>
</head>
<body>

<label>Кількість столів: <input type="number" id="tables" value="5" min="1"></label><br><br>
<select id="id_SelectElement" multiple style="width: 300px;"></select>

<script>
    let Select2MultiCheckBoxObj = [];
    const id_selectElement = 'id_SelectElement';
    const staticWordInID = 'state_';

    function AddItemInSelect2MultiCheckBoxObj(id, IsChecked) {
        let index = Select2MultiCheckBoxObj.findIndex(x => x.id == id);
        if (index > -1) {
            Select2MultiCheckBoxObj[index].IsChecked = IsChecked;
        } else {
            Select2MultiCheckBoxObj.push({ id: id, IsChecked: IsChecked });
        }
    }

    function generateTables(count) {
        const $select = $('#' + id_selectElement);
        $select.empty();
        for (let i = 1; i <= count; i++) {
            $select.append(`<option value="${i}">Стіл ${i}</option>`);
            AddItemInSelect2MultiCheckBoxObj(i, true);
        }
        $select.val([...Array(count).keys()].map(i => (i + 1).toString())).trigger("change");
    }

    function formatResult(state) {
        if (!state.id) return state.text;
        const isChecked = Select2MultiCheckBoxObj.find(x => x.id == state.id)?.IsChecked || false;
        return $(
            `<div class="checkbox">
        <input type="checkbox" id="${staticWordInID + state.id}" ${isChecked ? 'checked' : ''} />
        <label for="${staticWordInID + state.id}">${state.text}</label>
      </div>`
        );
    }

    $(document).ready(function () {
        generateTables(parseInt($('#tables').val(), 10));

        let $select2 = $('#' + id_selectElement).select2({
            templateResult: formatResult,
            closeOnSelect: false,
            width: '100%'
        });

        $('#tables').on('input', function () {
            generateTables(parseInt(this.value, 10) || 0);
        });

        $select2.on("select2:select", function (e) {
            $(`#${staticWordInID + e.params.data.id}`).prop("checked", true);
            AddItemInSelect2MultiCheckBoxObj(e.params.data.id, true);
        });

        $select2.on("select2:unselect", function (e) {
            $(`#${staticWordInID + e.params.data.id}`).prop("checked", false);
            AddItemInSelect2MultiCheckBoxObj(e.params.data.id, false);
        });

        $(document).on("click", ".select2-results__option", function (e) {
            const checkbox = $(this).find('input[type="checkbox"]');
            if (checkbox.length) checkbox.trigger('click');
        });

        $(document).on("click", ".select2Checkbox", function (e) {
            e.stopPropagation();
        });
        $(document).on("click", ".select2-results__option", function (e) {
            const data = $(this).data('data');
            if (!data || !data.id) return;

            const optionId = data.id;
            const $select = $('#' + id_selectElement);
            const option = $select.find(`option[value="${optionId}"]`);
            const isSelected = option.prop('selected');

            // обновляем select2, состояние и чекбокс
            option.prop('selected', !isSelected);
            AddItemInSelect2MultiCheckBoxObj(optionId, !isSelected);
            $(`#${staticWordInID + optionId}`).prop('checked', !isSelected);
            $select.trigger('change.select2');

            e.stopPropagation();
        });
        $(document).on("mousedown", ".select2-results__option", function (e) {
            e.preventDefault(); // ⛔ Остановим стандартное поведение Select2

            const data = $(this).data('data');
            if (!data || !data.id) return;

            const optionId = data.id;
            const $select = $('#' + id_selectElement);
            const option = $select.find(`option[value="${optionId}"]`);
            const isSelected = option.prop('selected');

            option.prop('selected', !isSelected);
            AddItemInSelect2MultiCheckBoxObj(optionId, !isSelected);
            $(`#${staticWordInID + optionId}`).prop('checked', !isSelected);
            $select.trigger('change.select2');
        });
    });
</script>
</body>
</html>
