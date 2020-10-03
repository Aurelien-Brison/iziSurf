var app = {

    chat: null,

    init: function() {

        console.log('init-js-chat');
        app.handleClickButtonSend();
        app.handleKeyUpToSend();
        app.handleClickButtonChat();
        app.handleClickClosechat();
        app.scrollbottom();

    },

    scrollbottom: function() {

        $target = $('#messages');
    
    },

    handleClickButtonChat: function () {
        $('#chat-button').click(function(e) {
            $(app.charger);
        })      
    },

    handleKeyUpToSend: function () {
        $('#message').keyup(function(e) {
            if(e.keyCode == 13)
            {

            const url = $('#form-chat').attr('action');

            
                $.ajax({
                    method:"post",
                    url: url,
                    data: { "message":
                    $('#message').val()
                    },
                    dataType: 'json',
                    success: function(statut, response, data) {
                        $('#form-chat')[0].reset();                      
                    }
        
                })
            }
        })
    },

    handleClickButtonSend: function () {
        
        $('#send').click(function(e) {
            e.preventDefault();
            
            var message = $('#message').val();
            const url = $('#form-chat').attr('action');
            var targetMessage = $('messages');
            
            $.ajax({
                method:"post",
                url: url,
                data: { "message":
                $('#message').val()
                },
                dataType: 'json',
                success: function(statut, response, data) {
                    $('#form-chat')[0].reset();              
                }
    
            })
        })
    },

    charger: function() {
        
      app.chat =  setTimeout(function(){
            const url = $('#form-load').attr('action');
            var lastmessage = $('#messages').children().last().data('id');
            $.ajax({
                method: "post",
                url : url,
                data: { "lastId":
                    lastmessage
                },
                success : function(data){
                   
                    if(data.message == "pas de message"){
                        
                    } else {

                        if(lastmessage == data[0][0]){
    
                        } else {     
                            // Transforme le format des dates de recherche
                            moment.locale('fr')
                            let date= new Date();
                            var options = { weekday: 'short', month: 'short', day: 'numeric' };
                            let dateNow = date.toLocaleDateString('fr-FR', options);
                            let hours = date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                            console.log(dateNow, hours);
                            $('#messages').append("<p class= author-message user-"+data[0][1]+" data-id="+data[0][0]+"><span class=modal__chat-firstname>"+ data[0][2] +"</span><span class='js-full-date modal__chat-date'>" + dateNow + " " + hours + "</span><br>"+ data[0][3] + "</p>");
                        }
                    }
                
                    var heightMessage = $('#messages').children().last().height();
                    if($('#messages').height() > $target.scrollTop()){

                        
                    } else {
                        $target.scrollTop(($target.scrollTop() + heightMessage));
                    }
                    
                }
            });
            app.charger();
        }, 1500)
    },

    handleClickClosechat: function() {
        $('#close').click(function(e) {
            clearTimeout(app.chat)
            
        })
    },
}


$(app.init);