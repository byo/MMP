<?php

class rewriteController extends AbstractController
{

  public function runStrategy()
  {
    $this->askForRewrite();
    $this->rewriteSchemaFile();
    $this->rewriteMigrationFiles();
  }

  protected function rewriteSchemaFile()
  {
    echo "REWRITING: schema\n";
    $fname = Helper::get('savedir').'/schema.php';
    if(!file_exists($fname))
    {
      echo "File: {$fname} does not exist!\n";
      exit;
    }
    require_once $fname;

    $schema = new Schema();
    $content = $schema->getContent();
    $fname = Helper::get('savedir').'/schema.php';
    file_put_contents($fname, $content['queries']);
  }

  protected function rewriteMigrationFiles()
  {
    $migrations = Helper::getAllMigrations();

    foreach($migrations as $migration)
    {
      require_once Helper::get('savedir').'/migration'.$migration.'.php';
      $classname = 'Migration'.$migration;
      $migration = new $classname(null);
      $content = $migration->getContent();
      $noCustomCode = true;
      foreach( array('preUp','postUp','preDown','postDown') as $name )
      {
        if ( count($content[$name]) > 0 )
        {
          echo "SKIPPING: migration$migration.php contains custom code in $name!\n";
          $noCustomCode = false;
        }
      }
      if ( $noCustomCode )
      {
        echo "REWRITING: migration$migration.php\n";
        $filename = Helper::get('savedir') . "/migration{$migration}.php";
        $fileContent = Helper::createMigrationContent($migration, $content);
        file_put_contents($filename, $fileContent);
      }
    }
  }

  protected function askForRewrite()
  {
    $c='';
    do{
      if($c!="\n")
      {
        echo "This operation will rewrite schema.php and all migration files!\n";
        echo "It does not support dynamically created migrations!\n";
        echo "Make sure to have a backup of rewritten files.\n";
        echo "Shall I continue [y/n]? ";
      }
      $c = fread(STDIN, 1);

      if($c ==='Y' or $c==='y' ){
        return;
      }
      if($c ==='N' or $c==='n' ){
        echo "\nExit without any change\n"; exit;
      }

    }while(true);
  }
}
