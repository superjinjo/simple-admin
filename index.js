$(document).ready(function() {

    $('.deleteButton').on('click', function(event) {
        if(!confirm("Are you sure you want to delete this user?")) {
            event.preventDefault();
        }
    });

    //When you click "Edit" button, a "Cancel" and "Submit" button will appear and
    //text insde the td tags for that row turn into input elements
    $('.showEdit').on('click', function(event) {
        event.preventDefault();

        //clear any previously generated input elements so there are no duplicates
        $('.cancel').click();

        $(this).parent().find('.editButtons').show();
        $(this).hide();

        var row = $(this).parents('tr')[0];

        //change text into input elements
        textToInput(row, 'firstName');
        textToInput(row, 'lastName');
        textToInput(row, 'login');
        textToInput(row, 'password');
    });

    function textToInput(row, inputName) {
        var td = $(row).find('.' + inputName)[0];

        var input = $('<input type="text" name="'+inputName+'">');
        input.val($(td).data('value'));
        $(td).html('').append(input);
    }

    $('.cancel').on('click', function(event) {
        event.preventDefault();

        $(this).parent().siblings('.showEdit').show();
        $(this).parent().hide();

        inputToText();
    });

    //changes all inputs back into text
    function inputToText() {
        $('input[type="text"]').each(function() {
            $(this).parent().text($(this).parent().data('value'));
        });
    }

});