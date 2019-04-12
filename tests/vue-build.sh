#!/bin/bash

entryFiles=(
    "terms_of_service_admin"
    "terms_of_service_user"
)

for entryFile in "${entryFiles[@]}"
do
    cp "js/$entryFile.js" "js/$entryFile.back"
done

# Make the app
set -e
make

for entryFile in "${entryFiles[@]}"
do
    echo "Comparing $entryFile to the original"
    if ! diff -q "js/$entryFile.js" "js/$entryFile.back" &>/dev/null
    then
        echo "$entryFile.js build is NOT up-to-date! Please send the proper production build within the pull request"
        exit 2
    fi
    rm "js/$entryFile.back"
done

echo "Vue.JS builds are up-to-date"
