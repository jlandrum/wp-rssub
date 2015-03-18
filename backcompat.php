<?php
if (!method_exists($response,'http_response_code')) {
  public function http_response_code() {
    foreach ($this->headers as $header) {
      if (preg_match("#^HTTP/\S+\s+(\d\d\d)#i",$header,$matches)) {
        return $matches[1];
      }
    }
    return 200;
  }
}
?>