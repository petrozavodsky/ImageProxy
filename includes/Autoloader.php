<?php

namespace ImageProxy;

	class Autoloader {

		private $loader;
		private $space;
		private $file;

		public function __construct( $file, $className ) {
			$this->file  = $file;
			$this->space = $className;
			$this->autoload();
			$this->addNamespaceObject( "Base" , $this->space,'core' );
			$this->addNamespaceObject( "Utils" , $this->space,'core' );
			$this->addNamespaceObject( "Widgets" );
			$this->addNamespaceObject( "Classes" );
			$this->addNamespaceObject( "Admin" );
			$this->addNamespaceObject( "Сompatibility" );
		}

		/**
		 * @param string $val
		 *
		 * @return $this
		 */
		public function setSpace( $val ) {
			$this->space = $val;

			return $this;
		}

		/**
		 * @param string $dir
		 * @param mixed $space
		 */
		public function addNamespaceObject( $dir, $space = false, $path = false ) {
			$s = DIRECTORY_SEPARATOR;
			if ( ! $space ) {
				$space = $this->space;
			}
			if ( ! $path ) {
				$path = "src";
			}

			$this->loader->addNamespace(
				$space . "\\{$dir}",
				realpath( plugin_dir_path( $this->file ) ) . $s . $path . $s . $space . $s . $dir
			);
		}


		/**
		 * @return void
		 */
		public function autoload() {
			require_once( 'Autoload.php' );
			$this->loader = new Autoload();
			$this->loader->register();
		}

	}
