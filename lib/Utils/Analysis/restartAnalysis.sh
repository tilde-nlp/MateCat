#!/bin/bash

################
#  DEPRECATED  #
################

DIR=$( cd "$( dirname "$0")"; pwd )
cd ${DIR}
sudo /bin/bash killAnalysis.sh

# get last return code, if != 0 don't wait
rc=$?;
if [[ ${rc} -eq 0 ]]; then
    echo "Wait 10 seconds to daemons to die."
    sleep 10
fi

sudo /bin/bash startAnalysis.sh