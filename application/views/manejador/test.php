
   <style>
   .table-wrapper {
  max-height: 25em;
  overflow: auto;
  display:inline-block;
}
   .loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 75px;
  height: 75px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 1s linear infinite;
  margin:auto;
  left:0;
  right:0;
  top:0;
  bottom:0;
  position:fixed;
  display: none;
}

@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}</style>

<div class="loader"></div>
<div class="container ">

<div class="float-left border" style=" height: 15em;" >
        <h4 align="center">Instancias</h4>
        <select  class="selectpicker float-left " required name="conns[]" multiple data-live-search="true" data-actions-box="true" id="conn">
            <?php foreach($dbs as $number => $db) { ?>
            <option value="<?php echo $db['id'] ?>"><?php echo $db['db'] ?></option>
                        <?php } ?>
        </select>
</div>
            <br>
                
        <div class="float-right">
        <button class="btn btn-success " id="ejecutar">Ejecutar consulta</button>
                <br><br>
                <button class="btn btn-secondary" id="guardar">Guardar consulta</button>
                <br>
                <br>
                <a href="<?php echo base_url()?>manejador/consultas" target="_blank">
               <button class="btn btn-primary" >Ver Consultas Guardadas</button>
               </a> 
            </div>
        </div>
    <div class="container" align="center">
    
         <textarea placeholder="Escriba la consulta" class="form-control bg-dark text-light " style=" width: 600px;height: 400px;" id="query" ></textarea>
    </div>
    <br>

  
    <div id="mensajes"></div>
   



    <div id ="modalSave" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Guardar Consulta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="http://localhost/InstanceManagementApp/" method = "post" class = "form-horizontal" id="form1">
        	<div class = "container">
	        	<div class="form-group row">
					<div class ="col">
						<label class = "text-muted" 
						style="margin-top:10px;"> Guardar como: </label>		
						
		        		<input type="text" required name="saveAs" id = "saveAs" 
		        		class ="form-control"/>

			      		<label class = "text-muted" 
						style="margin-top:10px;"> Descripcion: </label>		
						
		        		<input type="text" required name="inputDetails" id = "details" 
		        		class ="form-control"/>
						
						<label class = "text-muted" 
						style="margin-top:10px;"> Fecha: </label>

		        		<input type="date" required name="fecha" id = "date" placeholder="Fecha" 
		        		class ="form-control"/>	
					</div>
					<div class = "col">
		        		<label class = "text-muted" 
						style="margin-left:2px; margin-top:10px;"> Consulta: </label>		
						
		        		<textarea type="text" required name="queryInfo" id = "queryInfo" 
		        		class ="form-control"
		        		style="height:300px; ">
		        		</textarea> 
					</div>
	        	</div>
        	</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSaveQuery" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>

$(document).ready(()=>{
    $(".alert").hide();
    
})

$("textarea").keydown(function(e) {
    if(e.keyCode === 9) { // tab was pressed
        // get caret position/selection
        var start = this.selectionStart;
        var end = this.selectionEnd;

        var $this = $(this);
        var value = $this.val();

        // set textarea value to: text before caret + tab + text after caret
        $this.val(value.substring(0, start)
                    + "\t"
                    + value.substring(end));

        // put caret at right position again (add one for the tab)
        this.selectionStart = this.selectionEnd = start + 1;

        // prevent the focus lose
        e.preventDefault();
    }

    if (e.ctrlKey && e.keyCode == 13) {
      $('#ejecutar').trigger("click");

  }
});
$("#guardar").click(()=>{
    $("#queryInfo").val($('#query').val());
    $('#modalSave').modal('show');
})

$("#btnSaveQuery").click(()=>{
    let nombreConsulta  =   $("#saveAs").val(),
        descripcion     =   $("#details").val(),
        fecha     =   $("#date").val(),
        consulta     =   $("#queryInfo").val()
        if(nombreConsulta =='' || descripcion =='' ||fecha =='' ||consulta =='' ){
            alert('Hay campos vacios.');
        }else {

        
        obj = {
            nombreConsulta  : nombreConsulta,
            descripcion     : descripcion,
            fecha           : fecha,
            consulta        : consulta
        };
    
    
    $.ajax({
            url:'<?php echo base_url()  ?>manejador/saveConsulta',
            method: 'post',
            data: obj,
            dataType: 'json',
            success: function(data){
                $('#modalSave').modal('hide');
            }
        
            })
        }
})
    $('#ejecutar').on('click',()=>{
        $('.loader').css('display','block');
        $('#mensajes').empty();
        $.ajax({
            url:'<?php echo base_url()  ?>manejador/ejecutar',
            method: 'post',
            data: {id: $('#conn').val(), query: $('#query').val()},
            dataType: 'json',
            success: function(data){
                $('.loader').css('display','none');

                data.respuesta.forEach((respuesta)=>{
                    if(respuesta.errores !=null){

                    respuesta.errores.forEach(error => { 
                        $('#mensajes').append(`
                        <div class="alert alert-danger error fade show" role="alert">
                                ${error}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                        `)
                    });
                    $(".error").show()
                      
                    } else {
                        if(respuesta.correcto != null && /<\/?[a-z][\s\S]*>/i.test(respuesta.correcto) != true ){

                        $('#mensajes').append(`
                        <div class="alert alert-success completada fade show" role="alert">
                    Consulta completada con exito
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                    </div>`);
                        $(".completada").show()
                            var duration = 2000; //2 seconds
                        setTimeout(function () { $('.completada').alert('close'); }, duration);
                        }else{
                            $('#mensajes').append(respuesta.correcto);
                        }
                    }
                })
                
                }
                     })

        });



  
</script>