// this is the id of the form
$("#formLogin").submit(function(e) {

    e.preventDefault(); // avoid to execute the actual submit of the form.

    var form = $(this);
    var actionUrl = form.attr('action');

    $.ajax({
        type: "POST",
        url: actionUrl,
        data: form.serialize(), // serializes the form's elements.
        success: function(data)
        {
            var result = JSON.parse(data)
            console.log(result)
            if(result.response === 1 ){

                if(result.role == "caisse"){
                    // window.location.reload()
                    window.location.href = "step2.php"

                }
                else{
                    window.location.href = "../admin/";
                }
            }
            else{
                $('#msgLogin').text(result.message).addClass('text-danger text-center')
            }
        }
    });

});

function setIdCaisse(idcaisse) {
    $.ajax({
        type: "POST",
        url: 'updateIdCaisse.php',
        contentType: "application/json",
        data: JSON.stringify({
            "idcaisse": idcaisse,
        }),
        success: function(data)
        {
            console.log(data)
            var result = JSON.parse(data)
            if(result.response === 1){
                var base_url = window.location.origin;
                if(result.type == 2 ){
                    window.location.href = base_url + '/caisse-backend/bar/caisse/restaurant.php'
                }else if(result.type == 1){
                    window.location.href = base_url + '/caisse-backend/bar/caisse/'
                }
            }
        }
    });
}
