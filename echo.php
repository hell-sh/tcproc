<?php
echo "Echoooo!!!\r\n";
$stdin = fopen("php://stdin", "r");
do
{
	echo fgets($stdin);
}
while(true);
