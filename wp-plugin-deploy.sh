#! /bin/bash
# Source: https://github.com/thenbrent/multisite-user-management/blob/master/deploy.sh
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

# Check if a cli command is available (0 = true | 1 = false)
function command_is_not_available() {
    if ! type "$1" > /dev/null;
    then
        return 0
    else
        return 1
    fi
}

# main config
PLUGINSLUG="wp-attachment-export"
CURRENTDIR=`pwd`
MAINFILE="wp-attachment-export.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG/" # Remote SVN repo on wordpress.org, with no trailing slash
SVNUSER="helvetian" # your svn username

# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy wordpress plugin"
echo 
echo ".........................................."
echo 

# Check if subversion is installed before getting all worked up
if command_is_not_available "svn"
	then
	echo "You'll need to install subversion before proceeding. Exiting....";
	exit 1;
fi

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" "$GITPATH/readme.txt" | awk -F' ' '{print $NF}'`
echo "readme.txt version: $NEWVERSION1"
NEWVERSION2=`grep "^Version:" "$GITPATH/$MAINFILE" | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ] && [ "$NEWVERSION1" != "trunk" ]; then echo "Version in readme.txt & $MAINFILE don't match. Exiting...."; exit 1; fi

if [ "$NEWVERSION1" == "trunk" ]
	then
		echo "Setting trunk version to $NEWVERSION2 ..."
		NEWVERSION1=$NEWVERSION2;
	else
		echo "Versions match in readme.txt and $MAINFILE ..."
fi

cd "$GITPATH"
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG

read -p "Create Git commit & tag? " -n 1 -r
echo # Move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
	then

		if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
			then 
				echo "Version $NEWVERSION1 already exists as git tag. Exiting...."; 
				exit 1; 
			else
				echo "Git version does not exist. Let's proceed..."
		fi

	    git commit -am "$COMMITMSG"

	    echo "Tagging new version in git"
	    git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

	    echo "Pushing latest commit to origin, with tags"
	    git push origin master
	    git push origin master --tags

fi

# Checkout SVN repo
echo 
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

# Check if the svn tag already exists
echo "Checking if the svn tag already exists ..."
set +e && svn info "$SVNURL/tags/$NEWVERSION1" >& /dev/null && set -e
if [ $? == 0 ]
	then
		echo "Version $NEWVERSION1 already exists as svn tag. Exiting....";
		exit 1;
	else
		echo "Version $NEWVERSION1 does not exist as svn tag. Proceeding....";
fi

echo "Clearing svn repo so we can overwrite it"
svn rm $SVNPATH/trunk/*

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring github specific files and deployment script"
svn propset svn:ignore "wp-plugin-deploy.sh
README.md
.git
.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn commit --username=$SVNUSER -m "$COMMITMSG"

echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/

echo "*** Plugin deployment complete ***"
