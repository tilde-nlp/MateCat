#!/bin/bash

echo "spawning daemons"
sudo -u www-data screen -d -m -S fast php FastAnalysis.php ../../../inc/task_manager_config.ini
sudo -u www-data screen -d -m -S tm php TmAnalysis.php ../../../inc/task_manager_config.ini

exit 0;