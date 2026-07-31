<div class="col-11" id="table_page">
	<div class="row" style="width: 100%;background: #FFFFFF;padding: 10px;">
			<button type="button" class="btn btn-block <?php echo !isset($_GET['status']) ? "bg-gradient-info" : "btn-outline-info" ?> btnTableStatus" onclick="showTable('all',this)" style="margin-top: 7px;">Tous</button>
			<button type="button" class="btn btn-block <?php echo isset($_GET['status']) && $_GET['status'] == "busy" ? "bg-gradient-info" : "btn-outline-info" ?> btnTableStatus" onclick="showTable('busy',this)">Occupé</button>
			<button type="button" class="btn btn-block <?php echo isset($_GET['status']) && $_GET['status'] == "free" ? "bg-gradient-info" : "btn-outline-info" ?> btnTableStatus" onclick="showTable('free',this)">Libre</button>
	</div>
	<div class="row" id="tablesbloc" style="padding: 20px;">
			<?php 
			$status = 404;
			if(isset($_GET['status']) && $_GET['status'] == "busy"){
				$status = 1;
			}
			elseif(isset($_GET['status']) && $_GET['status'] == "free"){
				$status = 0;
			}
			elseif(isset($_GET['status']) && $_GET['status'] == "all"){
				$status = 404;
			}

			if($status==404){
				$sql = "SELECT * FROM restaurant_tables";
			}elseif($status==1){
				$sql = "SELECT * FROM restaurant_tables r WHERE r.numero IN (select idtable from table_client_panier)";
			}else{
				$sql = "SELECT * FROM restaurant_tables r WHERE r.numero NOT IN (select idtable from table_client_panier)";
			}
			$query = $conn->query($sql);
			if ($query->num_rows>0) {
				while($table = $query->fetch_assoc()){
					?>
					<div class="<?php echo $table['status'] == 1 ? "current-table" : "tableStyle" ?> col-lg-2">
						<div>
							<h3 class="tableNumero">Table n° <?php echo $table['numero'] ?></h3>
							<p class="tablePlace" >Places: <span id="<?php echo 'table-'.$table['numero'] ?>"><?php echo $table['places'] ?></span>     <i class="fa-solid fa-pen-to-square" style="margin-left:10px;cursor: pointer;" onclick="$('#formUpdatePlace').css('display','flex')"></i></p>
							<div  id="formUpdatePlace" style="justify-content: center;margin-bottom: 10px;">
								<input type="number" class="form-control" id="updatePlace-<?php echo $table['numero'] ?>"  style="width:30%;">
								<input type="submit" class="btn btn-sm btn-default" style="margin-left:15px" name="btnPlace"  onclick="editPlace('<?php echo 'updatePlace-'.$table['numero'] ?>','<?php echo $table['numero'] ?>','<?php echo 'table-'.$table['numero'] ?>')" value="Valider">
							</div>
							<button type="button" class="btn btn-outline-primary btn-block selectTable" 
							onclick="selectTable(this,'<?php echo $table['id'] ?>','<?php echo $table['numero'] ?>','<?php echo $table['places'] ?>')">Séléctionner </button>
						</div>

					</div>
					<?php
				}
			}
			?>

	</div>	
</div>

