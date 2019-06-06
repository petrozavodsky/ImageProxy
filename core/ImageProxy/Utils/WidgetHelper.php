<?php

namespace ImageProxy\Utils;

trait WidgetHelper {

	use Assets;

	public function addWidgetAssets() {
		add_action( "wp", function () {
			if ( ! is_active_widget( 0, $this->id, $this->id_base ) === false ) {
				$this->addJsCss($this->id_base);
			}
		} );
	}

	public function addJsCss($base){
		if ( $this->css ) {
			$this->addCss( $base, "header" );
		}
		if ( $this->js ) {
			$this->addJs( $base, "header" );
		}
	}

}
