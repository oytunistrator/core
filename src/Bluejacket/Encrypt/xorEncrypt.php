<?php
/**
 * xorCrypt class.
 */
namespace Bluajacket\Framework\Encrypt;
class xorCrypt
{
	private $password = NULL;

	/**
	 * set_key function.
	 *
	 * @access public
	 * @param mixed $password
	 * @return void
	 */
	public function set_key($password) {
		$this->password = $password;
	}

	/**
	 * get_rnd_iv function.
	 *
	 * @access private
	 * @param mixed $iv_len
	 * @return void
	 */
	private function get_rnd_iv($iv_len) {
		$iv = '';
		while ($iv_len-- > 0) {
			$iv .= chr(mt_rand() & 0xff);
		}
		return $iv;
	}

	/**
	 * encrypt function.
	 *
	 * @access public
	 * @param mixed $plain_text
	 * @param int $iv_len (default: 16)
	 * @return void
	 */
	public function encrypt($plain_text, $iv_len = 16) {
		$plain_text .= "\x13";
		$n = strlen($plain_text);
		if ($n % 16) {
			$plain_text .= str_repeat("\0", 16 - ($n % 16));
			$i = 0;
			$enc_text = $this->get_rnd_iv($iv_len);
			$iv = substr($this->password ^ $enc_text, 0, 512);
			while ($i < $n) {
				$block = substr($plain_text, $i, 16) ^ pack('H*', sha1($iv));
				$enc_text .= $block;
				$iv = substr($block . $iv, 0, 512) ^ $this->password;
				$i += 16;
			}
			return base64_encode($enc_text);
		} else {}
	}

	/**
	 * decrypt function.
	 *
	 * @access public
	 * @param mixed $enc_text
	 * @param int $iv_len (default: 16)
	 * @return void
	 */
	public function decrypt($enc_text, $iv_len = 16) {
		$enc_text = base64_decode($enc_text);
		$n = strlen($enc_text);
		$i = $iv_len;
		$plain_text = '';
		$iv = substr($this->password ^ substr($enc_text, 0, $iv_len), 0, 512);
		while ($i < $n) {
			$block = substr($enc_text, $i, 16);
			$plain_text .= $block ^ pack('H*', sha1($iv));
			$iv = substr($block . $iv, 0, 512) ^ $this->password;
			$i += 16;
		}
		return stripslashes(preg_replace('/\\x13\\x00*$/', '', $plain_text));
	}
}
?>
