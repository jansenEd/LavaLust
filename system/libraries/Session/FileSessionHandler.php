<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 * 
 * Copyright (c) 2020 Ronald M. Marasigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://techron.info)
 * @version Version 1.2
 * @link https://lavalust.com
 * @license https://opensource.org/licenses/MIT MIT License
 */

class FileSessionHandler extends Session implements \SessionHandlerInterface {
    private $savePath, $data, $file_path;

    public function __construct()
    {
        $this->config =& get_config();

        if (!empty($this->config['sess_save_path'])) {
            $this->savePath = rtrim($this->config['sess_save_path'], '/\\');
            ini_set('session.sess_save_path', $this->savePath);
        } else {
            $this->savePath = rtrim(ini_get('session.save_path'), '/\\');
        }

    }

    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        $this->file_path = $this->savePath.DIRECTORY_SEPARATOR.$sessionName . '_';
        if ( !is_dir($this->savePath) ) {
            mkdir($this->savePath, 0700, TRUE);
        }
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $this->data = false;
        $filename = $this->file_path.$id;
        if ( file_exists($filename) ) $this->data = @file_get_contents($filename);
        if ( $this->data === false ) $this->data = '';

        return $this->data;
    }

    public function write($id, $data) {
        $filename = $this->file_path.$id;

        if ( $data !== $this->data ) {
            return @file_put_contents($filename, $data, LOCK_EX) === false ? false : true;
        }
        else return @touch($filename);
    }

    public function destroy($id) {
        $filename = $this->file_path . $id;
        if ( file_exists($filename) ) @unlink($filename);

        return true;
    }

    public function gc($maxlifetime) {
        foreach ( glob("$this->file_path*") as $filename ) {
            if ( filemtime($filename) + $maxlifetime < time() && file_exists($filename) ) {
                @unlink($filename);
            }
        }

        return true;
    }
}
?>