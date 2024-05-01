### Php Code Module
	* Here you will Find All the Php Based Re-use able Module
	* You can use this in your WordPress Plugin
	* You can use It in Laravel
	* You can Use it in Raw php
	* You can Use it In any php Based Project

### Module List

### ArafatConfigModule
	* In this module You use this as wp meta_config
	* Use this as Post_meta
	* I mean this module Can save Any Config Related data or Img
	* for Example you can Save a google scholar img based on Key
	* you can save a Footer Payment Img Based on Key
	* It has Automatic system that will save file and delete Previous if it id and Key is same
	* For Best Practise
	* It can Save Data Or Update date If The Id and Key Already Exist
	* it is a nice Module That will save lot of our Developing Time

### Arafat Config Module Example of Use Case

```php
<?php

// save data to config
ArafatConfigModule::getInstance()->update_config(1, 'my_key', 'Hello There');
// delete data from config
ArafatConfigModule::getInstance()->delete_previous_must_using_id_key(1, 'my_key');

// Retrive my saved logo list will retrive all entry
$logo = ArafatConfigModule::getInstance()->get_config_by_data_key('footer_momo_img');

// will retrive the first data_value from db
$logo = ArafatConfigModule::getInstance()->get_single_val($logo);

// Retrive my saved logo single will retrive single entry
$logo = ArafatConfigModule::getInstance()->get_config_by_data_key_single('footer_momo_img');
// will retrive the first data_value from db
$logo = ArafatConfigModule::getInstance()->get_single_val($logo);

# Lets Upload a File That we will use
# A file can be any file .. img pdf docs any kind of files

$path = public_path('img');
$db_img_path = 'img';

ArafatConfigModule::getInstance()->update_config_file($_FILES, $path, $db_img_path, 0, 'footer_momo_img');

// Enjoy it ... 
```