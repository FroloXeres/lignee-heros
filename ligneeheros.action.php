<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * ligneeheros implementation : © FroloX nico.cleve@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * ligneeheros.action.php
 *
 * ligneeheros main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/ligneeheros/ligneeheros/myAction.html", ...)
 *
 */
  
  
  class action_ligneeheros extends APP_GameAction
  {
      /**
       * @var callable[]
       */
      protected array $actionMethods = [];

      /**
       * Action init
       */
      public function __construct()
      {
          parent::__construct();

          $this->actionMethods = $this->game->getStateService()->getCleanActionMethods($this);
      }

    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "ligneeheros_ligneeheros";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
    /**
    * @param string $name
    * @param array  $arguments
    *
    * @return void
    */
    function __call(string $name, array $arguments) {
        if (array_key_exists($name, $this->actionMethods)) {
            self::setAjaxMode();

            // Find a way to list args
            call_user_func_array($this->actionMethods[$name], $arguments);

            self::ajaxResponse();
        }
    }

    /*
    // Example:
    public function myAction()
    {
        self::setAjaxMode();

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    */
}
