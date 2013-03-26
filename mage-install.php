#!/usr/bin/env php
<?php
/**
 * Postinstall script for magento on dotcloud
 *
 */

echo "Post Install script started \n";

$environment_variables_path = '/home/dotcloud/environment.json';
$doc_root = '/home/dotcloud/current/';
$db_name = 'magento';

if (file_exists($environment_variables_path))
{
	$environment_variables = json_decode(file_get_contents("/home/dotcloud/environment.json"));
	echo "dotcloud environment variables file found & decoded \n";
}


/**
 * Create empty magento database ready for new install
 * 
 * @return void
 * 
 */
function create_database($environment_variables, $db_name)
{
	echo $environment_variables->DOTCLOUD_DB_MYSQL_HOST;
	$dsn = "mysql:host=" . $environment_variables->DOTCLOUD_DB_MYSQL_HOST . ";port=" . $environment_variables->DOTCLOUD_DB_MYSQL_PORT;

    $password = $environment_variables->DOTCLOUD_DB_MYSQL_PASSWORD;
    $user 	  = $environment_variables->DOTCLOUD_DB_MYSQL_LOGIN;

    try
    {	
    	$dbh = new PDO($dsn, $user, $password);
    	$dbh->exec("CREATE DATABASE IF NOT EXISTS " . $db_name) or die("Error creating DB");
    } 
    catch (Exception $e)
    {
    	echo "Error creating DB: " . $e->getMessage();
    }
}

/**
 * Run magento PHP CLI installer
 * 
 * @param  array $environment_variables dotcloud environment variables
 * 
 * @return void
 */
function run_install($environment_variables, $doc_root)
{
	$options_list = array(
				"--license_agreement_accepted" => "yes",
            	"--locale" => "en_GB",
            	"--timezone" => "Europe/London",
            	"--default_currency" => "GBP",
            	"--db_host" => $environment_variables->DOTCLOUD_DB_MYSQL_HOST,
            	"--db_name" => 'magento',
            	"--db_user" => $environment_variables->DOTCLOUD_DB_MYSQL_LOGIN,
            	"--db_pass" => $environment_variables->DOTCLOUD_DB_MYSQL_PASSWORD,
            	"--session_save" => "db",
            	"--admin_frontname" => "system",
            	"--url" => $environment_variables->DOTCLOUD_WWW_HTTP_URL,
            	"--use_rewrites" => "no", # leave rewrites off for now
            	"--use_secure" => "yes",
            	"--secure_base_url" => $environment_variables->DOTCLOUD_WWW_HTTP_URL,
            	"--use_secure_admin" => "yes",
            	"--admin_firstname" => "admin",
            	"--admin_lastname" => "admin",
            	"--admin_email" => $environment_variables->DOTCLOUD_EMAIL,
            	"--admin_username" => "admin",
            	"--admin_password" => "test123",
            	"--allow_symlink" => "1",
            	"--encryption_key" => "BRuvuCrUd4aSWutr"
        	);

	$options = "";
	foreach ($options_list as $option => $value)
	{
		$options = $options . " " . $option . " " . "'" . $value . "'";
	}
	
	$cmd = "php -f " . $doc_root . "install.php -- " . $options;
	system($cmd);

	echo "Magento CLI installer complete \n ";
}

echo "Creating database \n";
create_database($environment_variables, $db_name);

echo "Running magento CLI installer \n";
run_install($environment_variables, $doc_root);

echo "done! ";

?>
