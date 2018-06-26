<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>QA</title>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">

  <style>
    html,
    body {
      height: 100%;
    }

    body {
      display: -ms-flexbox;
      display: flex;
    }
  </style>
</head>
<body class="text-center h-100">
  
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
      <main role="main" class="inner cover container-fluid h-100">
        <div class="row align-items-center justify-content-center h-50">
          <div class="col col-sm-8 col-md-8 col-lg-6 col-xl-4">
            <form action="">
              <div class="form-group">
                <input class="form-control form-control-lg" placeholder="Pergunta" type="text" name="question" id="questionToAnswer">
              </div>
              <div class="form-group">
                <a class="btn btn-success btn-lg btn-block text-white" id="btn_gravar_audio">Gravar Pergunta</a>
                <button class="btn btn-info btn-lg btn-block" id="btnAskQuestion">Perguntar</button>
              </div>
            </form>
          </div>
        </div>
        <div class="text-center pt-2 mt-2 text-message font-weight-regular">
          <p id="answer"></p>
          <p id="answerText"></p>
        </div>
      </main>
  </div>

  <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

  <script>
    $(document).ready(function() {
      $('#btnAskQuestion').on('click', function(e) {
        $('#answer').html('');
        $('#answerText').html('');
        let self = this;
        $('#btn_gravar_audio').attr('disabled', true);
        e.preventDefault();
        let question = $('#questionToAnswer').val();
        $('#questionToAnswer').attr('disabled', true);
        $('#questionToAnswer').attr('readonly', true);

        let url = 'https://40.76.88.168:5000/answer?question='+question;

        self.disabled = true;

        $.get(url, function(resp) {
          let json = JSON.parse(resp);

          $('#answer').html(`<b>Resposta: </b><span>${json.answer[0].answer}</span>`);
          $('#answerText').html(`<b>Contexto: </b><span>${json.answer[0].context.text}</span>`);

          self.disabled = false;
          $('#btn_gravar_audio').attr('disabled', false);
          $('#questionToAnswer').attr('disabled', false);
          $('#questionToAnswer').attr('readonly', false);
        });
      });

      var btn_gravacao = document.querySelector('#btn_gravar_audio');
      // Crio a variavel que amarzenara a transcrição do audio
      var transcricao_audio =  '';
      // Seto o valor false para a variavel esta_gravando para fazermos a validação se iniciou a gravação
      var esta_gravando = false;
      // Verificamos se o navegador tem suporte ao Speech API
      if(window.SpeechRecognition || window.webkitSpeechRecognition){
        // Como não sabemos qual biblioteca usada pelo navegador 
        // Atribuimos a api retornada pelo navegador
        var speech_api = window.SpeechRecognition || window.webkitSpeechRecognition;
        // Criamos um novo objeto com a API Speech
        var recebe_audio = new speech_api();
        // Defino se a gravação sera continua ou não
        // Caso deixamos ela definida como false a gravação tera um tempo estimado 
        // de 4 a 5 segundos
        recebe_audio.continuous = true;
        // Especifico se o resultado final pode ser alterado ou não pela compreenção da api
        recebe_audio.interimResults = true;
        // Especifico o idioma utilizado pelo usuario
        recebe_audio.lang = "pt-BR";
        // uso o metodo onstart para setar a minha variavel esta_gravando como true
        // e modificar o texto do botão
        recebe_audio.onstart = function(){
          esta_gravando = true;
          btn_gravacao.innerHTML = 'Gravando! Parar gravação.';
        };
        // uso o metodo onend para setar a minha variavel esta_gravando como false
        // e modificar o texto do botão
        recebe_audio.onend = function(){
          esta_gravando = false;
          btn_gravacao.innerHTML = 'Iniciar Gravação';
        };


        recebe_audio.onerror = function(event){
          console.log(event.error);
        };
        
        // Com o metodo onresult posso capturar a transcrição do resultado 
        recebe_audio.onresult = function(event){
          // Defino a minha variavel interim_transcript como vazia
          var interim_transcript = '';
          // Utilizo o for para contatenar os resultados da transcrição 
          for(var i = event.resultIndex; i < event.results.length; i++){
              // verifico se o parametro isFinal esta setado como true com isso identico se é o final captura
              if(event.results[i].isFinal){
                // Contateno o resultado final da transcrição
                transcricao_audio += event.results[i][0].transcript;
              }else{
                // caso ainda não seja o resultado final vou contatenado os resultados obtidos
                interim_transcript += event.results[i][0].transcript;
              }
              // Verifico qual das variaveis não esta vazia e atribuo ela no variavel resultado
              var resultado = transcricao_audio || interim_transcript;
              // Escrevo o resultado no campo da textarea
            document.getElementById('questionToAnswer').value = resultado;
          }

        };
        // Capturamos a ação do click no botão e iniciamos a gravação ou a paramos
        // dependendo da variavel de controle esta_gravando
        btn_gravacao.addEventListener('click', function(e){
          // Verifico se esta gravando ou não
          if(esta_gravando){
            // Se estiver gravando mando parar a gravação
            recebe_audio.stop();
            // Dou um retun para sair da função
            return;
          }
          // Caso não esteja capturando o audio inicio a transcrição
          recebe_audio.start();
        }, false);

      }else{
        // Caso não o navegador não apresente suporte ao Speech API apresentamos a seguinte mensagem
        console.log('navegador não apresenta suporte a web speech api');
        // alert('Este navegador não apresenta suporte para essa funcionalidade ainda');
      }
    });
  </script>
</body>
</html>