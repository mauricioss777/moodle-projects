<?php

namespace mod_presentation\output;

use renderable;
use templatable;

/**
 * This file contains the definition for the renderable classes for the sections page.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class mod_presentation_presentation implements renderable, templatable
{

    protected $id;
    protected $urls;
    protected $download = false;

    public function __construct($id, $urls, $download)
    {
        $this->id = $id;
        $this->urls = $urls;
        $this->download = $download;
    }

    public function export_for_template(\renderer_base $output)
    {
        $export = new \stdClass();
        $export->id = $this->id;
        $export->urls = $this->urls;
        $export->download = $this->download;

        return $export;
    }
}