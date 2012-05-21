SilverStripe Build Tools
========================

This is a fork of the code at https://github.com/silverstripe-australia/silverstripe-base, designed to be incorporated into a project as a module rather than being the root directory.

It requires Phing, and provides the following features:

 * Bootstrapping of a new project by creating a Phing-compatible build.xml file
 * Management of modules loaded as nested git repositories
 * Running SilverStripe tests via phpunit, including TeamCity integration (this is the CI server that SilverStripe runs its builds on)
 * Generation of .tar.gz and .zip archives of a project

As well as using it to manage the main SilverStripe download, it can used to manage SilverStripe projects.

How to use this repository
--------------------------

 * Check the repository into a subdirectory of your project, for example as a piston import or as a separate git repo in a subdirectory.
 * Run the "install" script within the repository to create a stub build.xml in your project root.
 * Install Phing
 * Run 'phing phpunit'

To use the 'phing upload-archive' task, edit the "buildrepository" setting in build.xml