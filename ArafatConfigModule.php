<?php

namespace extras\Modules;
use Illuminate\Support\Facades\DB;

# Other wise Laravel will throw error
use PDO;

class ArafatConfigModule
{
    private static $instance;
    private $connection;

    private function __construct()
    {

        # My custom Way
        // $this->connection = $this->db_connect();

        # Laravel Way
        $this->connection = DB::connection()->getPdo();

        # Database Table
        $this->createTableIfNotExists();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ArafatConfigModule();
        }
        return self::$instance;
    }

    public function pr($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    # If you want to use your own db connection
    private function db_connect(){

        $db_host = env('DB_HOST');
        $db_name = env('DB_DATABASE');
        $db_user = env('DB_USERNAME');
        $db_pass = env('DB_PASSWORD');

        try{
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        }catch(PDOException $e) {
            // Handle database connection errors
            echo "Connection failed: " . $e->getMessage();

            return;
        }

    }

    public function test(){

        $this->pr($this->connection);
        die('Hello World I am from Arafat Module');
    }

    private function createTableIfNotExists()
    {
        $tableName = 'arafat_config';
        $query = "
            CREATE TABLE IF NOT EXISTS $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                data_id VARCHAR(255) DEFAULT NULL,
                data_key VARCHAR(255) DEFAULT NULL,
                data_value LONGTEXT DEFAULT NULL,
                user_id TEXT DEFAULT NULL,
                data_id_for_table VARCHAR(255) DEFAULT NULL,
                guid VARCHAR(255) DEFAULT NULL,
                data_status VARCHAR(255) DEFAULT 'publish',
                data_config_type VARCHAR(255) DEFAULT 'config_data',
                data_password VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_data_id_key` (`data_id`, `data_key`)
            )
        ";

        $this->connection->exec($query);
    }


    public function update_config($data_id, $data_key, $data_val, $user_id = null, $data_id_for_table = null,  $data_config_type = 'config_data')
    {
        $stmt = $this->connection->prepare("
            INSERT INTO arafat_config (data_id, data_key, data_value, user_id, data_id_for_table, data_config_type, created_at, updated_at)
            VALUES (:data_id, :data_key, :data_value, :user_id, :data_id_for_table, :data_config_type, NOW(), NOW())
            ON DUPLICATE KEY UPDATE data_value = CASE 
                WHEN data_id = :data_id AND data_key = :data_key THEN :data_value 
                ELSE data_value 
            END, user_id = CASE 
                WHEN data_id = :data_id AND data_key = :data_key THEN :user_id 
                ELSE user_id 
            END, updated_at = NOW()
        ");

        $stmt->bindParam(':data_id', $data_id);
        $stmt->bindParam(':data_key', $data_key);
        $stmt->bindParam(':data_value', $data_val);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':data_id_for_table', $data_id_for_table);
        $stmt->bindParam(':data_config_type', $data_config_type);

        $res = $stmt->execute();

        return $res;
    }

    public function update_config_raw( array $data = [] )
    {

        if( !$data ){
            return false;
        }

        $arr_kyes = array_keys( $data );

        $col_names = implode( ', ', $arr_kyes );

        $col_name_placeholders = '';

        $col_name_placeholders = '';
        foreach( $arr_kyes as $v ){
            $col_name_placeholders .= ':'.$v.', ';
        }

        $col_name_placeholders = rtrim( $col_name_placeholders, ', ' );


        $stmt = $this->connection->prepare("
            INSERT INTO arafat_config ($col_names)
            VALUES ($col_name_placeholders)
            ON DUPLICATE KEY UPDATE data_value = CASE 
                WHEN data_id = :data_id AND data_key = :data_key THEN :data_value 
                ELSE data_value 
            END
        ");

        # Bind the data Dynamically
        $all_bind_param_arr = array();

        foreach( $data as $k => $v ){
            
            $col_placeholder = ':'.$k;
            $all_bind_param_arr[$col_placeholder] =  $v;

        }

        // $this->pr($all_bind_param_arr); die('die here');
        # End Binding data dynamically
        $res = $stmt->execute($all_bind_param_arr);

        return $res;
    }


    public function delete_previous_must_using_id_key( $data_id, $data_key ){

        // Prepare the statement
        $stmt = $this->connection->prepare("
            DELETE FROM arafat_config
            WHERE data_id = :data_id
            AND data_key = :data_key
        ");

        // Bind values to placeholders
        $stmt->bindValue(':data_id', $data_id);
        $stmt->bindValue(':data_key', $data_key);

        // Execute the statement
        $res = $stmt->execute();

        return $res;

    }

    public function get_config_by_data_id($data_id)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_id_single($data_id)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_key($data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_key = :data_key");
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_key_single($data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_key = :data_key");
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_id_and_key($data_id, $data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id AND data_key = :data_key");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_id_and_key_single($data_id, $data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id AND data_key = :data_key");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function get_config_by_data_id_or_key($data_id, $data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id OR data_key = :data_key");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_config_by_data_id_or_key_single($data_id, $data_key)
    {
        $stmt = $this->connection->prepare("SELECT * FROM arafat_config WHERE data_id = :data_id OR data_key = :data_key");
        $stmt->bindParam(':data_id', $data_id);
        $stmt->bindParam(':data_key', $data_key);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_single_val( $data ){
        $single_val = '';
        if(  is_array( $data )  && isset( $data[0] ) ){
            $single_val = $data[0]['data_value'];
        }else if( is_array( $data ) && $data['data_value'] ){
            $single_val = $data['data_value'];
        }
        return $single_val;
    }

    public function update_config_file( $file, $path, $db_img_path, $data_id, $data_key )
    {

        # Check if Director Path Exist
        if (!is_dir($path)) {
           mkdir($path, 0777, true);
        }

       /**
        *
        * If Same data_id and Data_key Match then delete Previous
        * Other wise db updated file also Uploaded but old file 
        * Exist in the Folder and It is Bad Practise
        *
        */
       
        $old_img_path = $this->get_config_by_data_id_and_key_single( $data_id, $data_key );

        if( $old_img_path ){

            $old_img_path = $old_img_path['data_value'];

            # We are passing the path so get the path from there
            // $path
            // Now in db We have some_folder then img
            // Remove some folder and get the name only

            // $this->pr($path);
            // $this->pr($old_img_path);

            // path 'some/folder/abc/right/img/66324b17.png';
            // will return 66324b17.png  No matter how deep the folder is
            // Or Here we have the $db_img_path So if we do
            // str_replace( $db_img_path, '',  $old_img_path)
            // We will get the img name .. It is So Simple Haha

            $img_name = basename($old_img_path);

            $img_to_check = $path.'/'.$img_name;

            // $this->pr( $img_to_check );

            if( file_exists($img_to_check) ){
                unlink($img_to_check);
            }
            
        }

        # Delete Olds


        # check file Upload Error
        if ($file['file']['error'] === UPLOAD_ERR_OK) {

            $unique_name = substr(uniqid(), 0, 8); // Generate unique 8-digit name
            $file_extension = pathinfo($file['file']['name'], PATHINFO_EXTENSION); // Get file extension
            $file_name = $unique_name . '.' . $file_extension; // Concatenate unique name with extension
            
            $img_path = $path . '/' . $file_name; // Full path to store the file
            move_uploaded_file($file['file']['tmp_name'], $img_path);

            # Db Img Path
            $db_img_path = $db_img_path.'/' . $file_name;

            $this->update_config($data_id, $data_key, $db_img_path, null, null, 'file_img');

        }
    }
}