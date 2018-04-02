<?php
namespace selio\page;

/**
 * This class allows for an easy creation of
 * page component classes that hold a reference
 * to the current page instance and allow for the
 * usage of its features. It is also used to provide
 * page's content when routing.
 */
abstract class PageComponent extends Component {
    /**
     * Method should output the component's HTML contents and perform its main
     * functionality.
     *
     * Method is called by Selio in place of the page's content method when
     * routing the page. It is provided with the URL arguments in this case.
     */
    public function build() { return; }

    /**
     * Method should perform any component-specific initialization.
     *
     * It is called by Selio after the page's initialization method when routing
     * the page. It is provided with the URL arguments in this case and any output
     * is discarded
     */
    public function initialize() { return; }

    /**
     * Method should perform any final actions or component-specific explicit
     * cleanup.
     *
     * It is called by Selio after the page's finalization method when
     * routing the page. It is provided with the URL arguments in this case and
     * any output id discarded.
     */
    public function finalize() { return; }
}