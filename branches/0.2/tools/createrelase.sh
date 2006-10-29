function testIn () {
	for t in $2
	do
		if [ "$t" = "$1" ]
		then
			return 0
		fi
	done
	return 1
}


rel=$1
tempDir='temp-dirs'
svn export ../ $tempDir  > /dev/null
includedskins=('default')
includedplugins=('helloWorld')
for dir in ${array[@]}
do
	echo $dir
done

cd $tempDir
# delete non-used dirs
rm -rf cache configs docs logs tools
# delete non-release skins
for skin in `dir skins`
do
	a=0
	`testIn $skin $includedskins` && a=1
	if [ $a = 0 ]
	then
		rm -rf skins/$skin
	fi
done

# delele non-release plugins
for plugin in `dir plugins`
do
	a=0
	`testIn $plugin $includedplugins` && a=1
	if [ $a = 0 ]
	then
		rm -rf plugins/$plugin
	fi
done

#create zip
tar -czf ../morgos-$rel.tar.gz * 

#remove temp dir
cd ..
rm -rf $tempDir
echo Ouput file written to morgos-$rel.tar.gz

