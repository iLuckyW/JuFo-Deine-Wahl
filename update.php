<?php
  if (isset($_GET['run']) && $_GET['run']) {
    shell_exec('su -c "chmod +x update.sh" deine-wahl 2>&1');
    shell_exec('su -c ./update.sh deine-wahl 2>&1');
  }
?>

<a href="?run=true">Update Server!</a>
