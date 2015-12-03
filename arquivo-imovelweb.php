<?php
// XML PARA EXPORTA��O DE IMOVEIS PARA O PORTAL IMOVELWEB
include "conexao.php";
$endereco = "http://".$_SERVER['SERVER_NAME']."/";

function removerCaracter($str){
  $remover = array("�" => "a","�" => "a","�" => "a","�" => "a","�" => "e","�" => "e","�" => 
                   "i","�" => "i","�" => "o","�" => "o","�" => "o","�" => "u","�" => "u","�" => 
                   "c","�" => "A","�" => "A","�" => "A","�" => "A","�" => "E","�" => "E","�" => 
                   "I","�" => "O","�" => "O","�" => "O","�" => "U","�" => "U","�" => "U"," " => "-");
  return str_replace("�", "", str_replace(",", "", str_replace(".", "",strtolower(strtr($str, $remover)))));
 }

$sql = mysql_query("SELECT i.*, date_format(i.datacadastro, '%d/%m/%Y') AS datacadastro, date_format(i.dataatualizacao, '%d/%m/%Y') AS dataatualizacao, 
	                t.tipo AS tipo, c.nome AS cidade, f.fase AS fase, b.nome AS bairro, p.imagem AS imgprincipal, u.nome AS corretor, if(i.negociacao_id=1, 'Vender', 'Alugar') AS negociacao
                    FROM tb_imoveis AS i INNER JOIN tb_tipos AS t
                    ON t.id = i.tipo_id
                    INNER JOIN tb_fases AS f
                    ON f.id = i.fase_id
                    INNER JOIN tb_cidade AS c
                    ON c.id = i.cidade_id
                    INNER JOIN tb_bairro AS b
                    ON b.id = i.bairro_id
                    INNER JOIN tb_imagens AS p
                    ON p.id = i.imagem
                    INNER JOIN tb_usuarios AS u
                    ON u.id = i.user01_id 
                    WHERE vendido = 0 ORDER BY id") or die(mysql_error());
		

//Abrindo documento xml
$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
 
//Abre bloco do xml
$xml .= "<ListingDataFeed xmlns=\"http://www.vivareal.com/schemas/1.0/VRSync\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.vivareal.com/schemas/1.0/VRSync http://xml.vivareal.com/vrsync.xsd\">";

		$xml .= "<Header>";
			$xml .= "<Provider> </Provider>";
			$xml .= "<Email> </Email>";
			$xml .= "<Telephone></Telephone>";
			$xml .= "<Logo>http://www.cinataimoveis.com.br/assets/images/logo.png</Logo>";
		$xml .= "</Header>";
		$xml .= "<Listings>";

	while ($row = mysql_fetch_array($sql)){
			
			$string = $row['tipo']."-com-".$row['quarto_id']."-quartos-no-bairro-".$row['bairro']."-em-".$row['cidade']."-com-".$row['areaconstruida']."m2";
			$string = removerCaracter($string);

			$xml .= "<Listing>";
				$xml .= "<ListingID>$row[id]</ListingID>";
				$xml .= "<Title>Cinata Im�veis</Title>";
				if($row['negociacao_id'] == 1 ){	
					$xml .= "<TransactionType>For Sale</TransactionType>";
				}else{
					$xml .= "<TransactionType>For Rent</TransactionType>";
				}
				$xml .= "<DetailViewUrl><![CDATA[http://www.cinataimoveis.com.br/imovel/$row[id]/".$string."]]></DetailViewUrl>";
				$xml .= "<Media>";

					$xml .= "<Item medium=\"image\" caption=\"Cinata Im�veis\" primary=\"true\">http://www.cinataimoveis.com.br/admin/includes/upload/$row[imgprincipal]</Item>";

				$query = mysql_query("SELECT * FROM tb_imagens WHERE imovel_id = $row[id] AND id <> $row[imagem]") or die(mysql_error());	
					while($linha = mysql_fetch_array($query)){	
						$xml .= "<Item medium=\"image\" caption=\"Cinata Im�veis\">http://www.cinataimoveis.com.br/admin/includes/upload/$linha[imagem]</Item>";
				    }

				$xml .= "</Media>";
				$xml .= "<Details>";
					if($row['tipo'] == 'Apartamento' ){
						$xml .= "<PropertyType>Residential / Apartment</PropertyType>";
					}
					if($row['tipo'] == 'Casa' ){
						$xml .= "<PropertyType>Residential / Home</PropertyType>";
					}
					if($row['tipo'] == 'Terreno' ){
						$xml .= "<PropertyType>Commercial / Building</PropertyType>";
					}
					if($row['tipo'] == 'Cobertura' ){
						$xml .= "<PropertyType>Residential / Apartment</PropertyType>";
					}
					if($row['tipo'] == 'Comercial' ){
						$xml .= "<PropertyType>Commercial / Business</PropertyType>";
					}
					if($row['tipo'] == 'Flat' ){
						$xml .= "<PropertyType>Residential / Flat</PropertyType>";
					}
					if($row['tipo'] == 'Galp�o' ){
						$xml .= "<PropertyType>Residential / Hangar</PropertyType>";
					}
					if($row['tipo'] == 'Granja' ){
						$xml .= "<PropertyType>Residential / Farm Ranch</PropertyType>";
					}
					if($row['descricao'] != "" ){												
						$xml .= "<Description><![CDATA[".utf8_decode(strip_tags(str_replace('&nbsp;', ' ', $row['descricao'])))."]]></Description>";
					}else{
						$xml .= "<Description><![CDATA[Os melhores im�veis VOC� ENCONTRA AQUI. Cinata Im�veis]]></Description>";
					}
					if($row['negociacao_id'] == "1" ){	
						$xml .= "<ListPrice currency=\"BRL\">".str_replace('.', '', doubleval($row[valor]))."</ListPrice>";//currency="BRL"
					}else{
						$xml .= "<RentalPrice currency=\"BRL\" period=\"Monthly\">".str_replace('.', '', doubleval($row[valor]))."</RentalPrice>";
					}
					$xml .= "<LotArea unit=\"square metres\">".str_replace(',', '.', $row[areaconstruida])."</LotArea>";//unit="square metres"
					$xml .= "<LivingArea unit=\"square metres\">".str_replace(',', '.', $row[areaconstruida])."</LivingArea>";
					$xml .= "<Bedrooms>$row[quarto_id]</Bedrooms>";
					$xml .= "<Bathrooms>$row[suite_id]</Bathrooms>";
					$xml .= "<Garage>$row[vaga_id]</Garage>";// type="Parking Space"
				$xml .= "</Details>";
				$xml .= "<Location>";
					$xml .= "<Country abbreviation=\"BR\">BRASIL</Country>";//abbreviation="BR"
					$xml .= "<State>PARA�BA</State>";//abbreviation="BR"
					$xml .= "<City>".utf8_decode($row[cidade])."</City>";
					$xml .= "<Neighborhood>".utf8_decode($row[bairro])."</Neighborhood>";					
				$xml .= "</Location>";
				$xml .= "<ContactInfo>";
					$xml .= "<Name>Cinata Im�veis</Name>";
					$xml .= "<Email>contato@cinataimoveis.com.br</Email>";
					$xml .= "<Location>";
						$xml .= "<Country abbreviation=\"BR\">BRASIL</Country>";//abbreviation="BR"
						$xml .= "<State>PARA�BA</State>";//abbreviation="BR"
						$xml .= "<City>JO�O PESSOA</City>";	
					$xml .= "</Location>";				
				$xml .= "</ContactInfo>";								
			$xml .= "</Listing>";
		
	}//fecha while

//fechando bloco do xml
		$xml .= "</Listings>";
		
$xml .= "</ListingDataFeed>";

	file_put_contents('../../xml/iw_ofertas.xml',$xml);	

?>

