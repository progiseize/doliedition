<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2023-2024 	Anthony Damhet			<contact@progiseize.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \defgroup   doliedition Module DoliEdition
 *  \brief      DoliEdition module descriptor.
 *
 *  \file       htdocs/custom/doliedition/core/modules/modDoliEdition.class.php
 *  \ingroup    doliedition
 *  \brief      Description and activation file for module DoliEdition
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module DoliEdition
 */
class modDoliEdition extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        $this->db = $db;

        // Id for module (must be unique).
        // Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 300301; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'doliedition';

        // Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
        // It is used to group modules by family in module setup page
        $this->family = "Progiseize";

        // Module position in the family on 2 digits ('01', '10', '20', ...)
        $this->module_position = '02';

        // Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description
        $this->description = "doliEditionModuleDescription";
        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "doliEditionModuleDescription";

        // Author
        $this->editor_name = 'Progiseize';
        $this->editor_url = 'https://progiseize.fr';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
        $this->version = '1.0.3';
        // Url to the file with your last numberversion of this module
        $this->url_last_version = "https://progiseize.fr/modules_info/lastversion.php?module=".$this->numero;

        // Key used in llx_const table to save module status enabled/disabled 
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        // To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
        $this->picto = 'fa-calendar-alt_far_#6c6aa8';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array(
        // Set this to 1 if module has its own trigger directory (core/triggers)
        'triggers' => 0,
        // Set this to 1 if module has its own login method file (core/login)
        'login' => 0,
        // Set this to 1 if module has its own substitution function file (core/substitutions)
        'substitutions' => 0,
        // Set this to 1 if module has its own menus handler directory (core/menus)
        'menus' => 0,
        // Set this to 1 if module overwrite template dir (core/tpl)
        'tpl' => 0,
        // Set this to 1 if module has its own barcode directory (core/modules/barcode)
        'barcode' => 0,
        // Set this to 1 if module has its own models directory (core/modules/xxx)
        'models' => 0,
        // Set this to 1 if module has its own printing directory (core/modules/printing)
        'printing' => 0,
        // Set this to 1 if module has its own theme directory (theme)
        'theme' => 0,
        // Set this to relative path of css file if module has its own css file
        'css' => array(),
        // Set this to relative path of js file if module must load a js on all pages
        'js' => array(),
        // Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
        'hooks' => array('ordersuppliercard','invoicesuppliercard'),
        // Set this to 1 if features of module are opened to external users
        'moduleforexternal' => 0,
        );

        // Data directories to create when module is enabled.
        $this->dirs = array();

        // Config pages. Put here list of php page, stored into doliedition/admin directory, to use to setup module.
        $this->config_page_url = '';

        // Dependencies
        // A condition to hide module
        $this->hidden = false;
        // List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        $this->depends = array();
        $this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

        // The language file dedicated to your module
        $this->langfiles = array("doliedition@doliedition");

        // Prerequisites
        $this->phpmin = array(7, 0); // Minimum version of PHP required by module
        $this->need_dolibarr_version = array(14, -3); // Minimum version of Dolibarr required by module

        // Messages at activation
        $this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        $this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        //$this->always_enabled = true;                                // If true, can't be disabled

        // Constants
        // List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        $this->const = array();

        if (!isset($conf->doliedition) || !isset($conf->doliedition->enabled)) {
            $conf->doliedition = new stdClass();
            $conf->doliedition->enabled = 0;
        }

        // Array to add new pages in new tabs
        $this->tabs = array();

        // Dictionaries
        $this->dictionaries = array();

        // Boxes/Widgets
        $this->boxes = array();

        // Cronjobs (List of cron jobs entries to add when module is enabled)
        // unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
        $this->cronjobs = array();
        
        // Permissions provided by this module
        $this->rights = array();
        $r = 0;

        // Add here entries to declare new permissions        
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Lire'; // Permission label
        $this->rights[$r][4] = 'edition';
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->doliedition->edition->read)
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Créer / Modifier'; // Permission label
        $this->rights[$r][4] = 'edition';
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->doliedition->edition->write)
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Supprimer'; // Permission label
        $this->rights[$r][4] = 'edition';
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->doliedition->edition->delete)
        $r++;
        
        // Main menu entries to add
        $this->menu = array();
        $r = 0;
        // Add here entries to declare new menus
        $this->menu[$r]=array( 
            'fk_menu'=>'fk_mainmenu=tools',
            'type'=>'left',
            'titre'=> 'doliEditionManage',
            'mainmenu'=>'tools',
            'leftmenu'=> $this->rights_class,
            'url'=> '/doliedition/views/editions.php',
            'langs'=>'doliedition@doliedition',
            'position'=> $this->numero.''.$r,
            'enabled'=> '$conf->doliedition->enabled',
            'perms'=>'$user->rights->doliedition->edition->read',
            'user'=>2,
            'prefix' => '<span class="far fa-calendar-alt paddingright pictofixedwidth" style="color: #6c6aa8;"></span>',);
        $r++;


    }

    /**
     *  Function called when module is enabled.
     *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *  It also creates data directories
     *
     * @param  string $options Options when enabling module ('', 'noboxes')
     * @return int                 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs, $db, $user;

        $result = $this->_load_tables('/doliedition/sql/');
        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }

        // Create extrafields during init
        include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        $extrafields = new ExtraFields($this->db);

        $edition_elements = array('propal','propaldet','commande','commandedet','facture','facturedet','supplier_proposal','supplier_proposaldet','commande_fournisseur','commande_fournisseurdet','facture_fourn','facture_fourn_det','expensereport');
        foreach($edition_elements as $elementtype):
            $extra_expensereport = $extrafields->addExtraField('edition_num', 'doliEditionE', 'sellist', '01', '', $elementtype, 0, 0, '', array('options'=> array('doliedition:edition:edition::active=1 AND entity=$ENTITY$' =>'')), 1, '', 1, 0, '', '', 'doliedition@doliedition', '$conf->doliedition->enabled');
        endforeach;

        // Permissions
        $this->remove($options);        

        $sql = array();
        return $this->_init($sql, $options);
    }

    /**
     *  Function called when module is disabled.
     *  Remove from database constants, boxes and permissions from Dolibarr database.
     *  Data directories are not deleted
     *
     * @param  string $options Options when enabling module ('', 'noboxes')
     * @return int                 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}
