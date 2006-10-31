#! /bin/bash

function testIn () {
	i=0
	for t in $@
	do
		if [ $i != 0 ]
		then
			if [ "$t" = "$1" ]
			then
				return 0
			fi
		fi
		i=$i+1
	done
	return 1
}


rel=$1
tempDir='temp-dirs'
svn export ../ $tempDir  > /dev/null

for param in $@
do
	c=0
	a=`expr $param : "skins=\(.*\)"` && c=1
	if [ $c = 1 ]
	then
		extraskins=$( echo $a | awk 'BEGIN{ FS="," } { print $1 "\n" $2 }' ) # command substitution
	fi
	
	c=0
	a=`expr $param : "plugins=\(.*\)"` && c=1
	if [ $c = 1 ]
	then
		extraplugins=$( echo $a | awk 'BEGIN{ FS="," } { print $1 "\n" $2 }' ) # command substitution
	fi
done

includedskins='default '$extraskins
includedplugins='helloWorld '$extraplugins

cd $tempDir
# delete non-used dirs
rm -rf cache configs logs tools
# delete non-release skins
for skin in `dir skins`
do
	a=0
	testIn $skin $includedskins && a=1
	if [ $a = 0 ]
	then
		rm -rf skins/$skin
		echo "deleting " $skin
	else
		echo "leaving " $skin
	fi
done

# delele non-release plugins
for plugin in `dir plugins`
do
	a=0
	testIn $plugin $includedplugins && a=1
	if [ $a = 0 ]
	then
		rm -rf plugins/$plugin
		echo "deleting " $plugin
	else
		echo "leaving " $plugin
	fi
done

#create zip
tar -czf ../morgos-$rel.tar.gz * 

#remove temp dir
cd ..
rm -rf $tempDir
echo Ouput file written to morgos-$rel.tar.gz

