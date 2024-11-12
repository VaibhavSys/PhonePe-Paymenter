#!/bin/bash

FORCE=false
if [ "$1" == "--force" ]; then
  FORCE=true
fi

if [ -d "PhonePe" ]; then
  if [ "$FORCE" == true ]; then
    rm -r PhonePe
  else
    echo "Directory PhonePe already exists. Use --force to overwrite."
    exit 1
  fi
fi

if [ -f "PhonePe.zip" ]; then
  if [ "$FORCE" == true ]; then
    rm PhonePe.zip
  else
    echo "File PhonePe.zip already exists. Use --force to overwrite."
    exit 1
  fi
fi

mkdir PhonePe
cp PhonePe.php PhonePe
cp routes.php PhonePe
cp phonepe.svg PhonePe
cp README.md PhonePe
zip -r PhonePe.zip PhonePe
rm -r PhonePe
echo "Archive created successfully."
