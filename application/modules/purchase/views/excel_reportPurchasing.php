<?php
$date_title = $date_from;
if($date_from != $date_till){
	$date_title = $date_from.' to '.$date_till;
}

header("Content-Type:   application/excel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_title).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1370;
$total_cols = 15;
?>

<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td colspan="<?php echo $total_cols ?>">
					<div>
					
						<div class="title_report"><?php echo $report_name;?></div>		
						<div class="subtitle_report">
						<?php
						if($date_from == $date_till){
							echo 'Tanggal : '.$date_from;
						}else{
							echo 'Tanggal : '.$date_from.' s/d '.$date_till; 
						}
						?>	
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" rowspan="2" width="50">NO</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">TGL.FAKTUR</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">NO.FAKTUR</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="160">SUPPLIER</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="150">NOTA/SURAT.JALAN</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL BARANG</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">SUB TOTAL</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">DISCOUNT</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">TAX</td>			
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">SHIPPING</td>			
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">GRAND TOTAL</td>			
				<td class="tbl_head_td_xcenter" colspan="2">TOTAL PAYMENT</td>
				<td class="tbl_head_td" rowspan="2" width="100">TERMIN</td>
			</tr>
			<tr class="tbl-header">						
				<td class="tbl_head_td_xcenter" width="100">CASH</td>		
				<td class="tbl_head_td_xcenter" width="100">CREDIT</td>
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_item = 0;
				$total_qty = 0;
				$sub_total = 0;
				$total_discount = 0;
				$total_tax = 0;
				$total_shipping = 0;
				$grand_total = 0;
				$grand_total_cash = 0;
				$grand_total_credit = 0;
				foreach($report_data as $det){

					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['purchasing_date']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['purchasing_number']; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_name']; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_invoice']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_item']); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_sub_total_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_discount_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_tax_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_shipping_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_total_price_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_total_price_cash_text']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['purchasing_total_price_credit_text']; ?></td>
						<td class="tbl_data_td"><?php echo $det['purchasing_termin']; ?> Hari</td>
						
					</tr>
					<?php	
											
					$total_item += $det['total_item'];
					$total_qty += $det['total_qty'];
					$sub_total += $det['purchasing_sub_total'];
					$total_discount +=  $det['purchasing_discount'];
					$total_tax +=  $det['purchasing_tax'];
					$total_shipping +=  $det['purchasing_shipping'];
					$grand_total +=  $det['purchasing_total_price'];
					$grand_total_cash +=  $det['purchasing_total_price_cash'];
					$grand_total_credit +=  $det['purchasing_total_price_credit'];
					
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="5">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_item; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($sub_total); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_discount); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_tax); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_shipping); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($grand_total); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($grand_total_cash); ?></td>	
					<td class="tbl_summary_td_xright"><?php echo priceFormat($grand_total_credit); ?></td>					
					<td class="tbl_summary_td_xright">&nbsp;</td>
				</tr>
				<?php
			}else{
			?>
				<tr>
					<td colspan="<?php echo $total_cols; ?>" class="tbl_data_td_first_xcenter">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
			<tr>
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="<?php echo $total_cols-5; ?>">
				<br/><br/><br/>
				Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="3" class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="2" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
			</tr>
		
		</tbody>
	</table>
</div>
</body>
</html>