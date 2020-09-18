/**
 * @param string $regexToMatch
 * @param mixed ...$argv
 * @return $this
 * @throws PublicAlert
 */
public function regexMatch(string $regexToMatch, ...$argv): self
{
    $matches = [];

    try {
        if (1 > @preg_match_all($regexToMatch, $this->uri, $matches, PREG_SET_ORDER)) {  // can return 0 or false
            return $this;
        }
    } catch (Throwable $exception) {
        throw new PublicAlert('The following regex failed :: ' . $regexToMatch);
    }
    $this->matched = true;
    $matches = array_shift($matches);
    array_shift($matches);  // could care less about the full match
    // Variables captured in the path to match will passed to the closure
    if (is_callable($argv[0])) {
        $callable = array_shift($argv);
        $argv = array_merge($argv, $matches);
        call_user_func_array($callable, $argv); // I'm ignoring this return now,
        return $this;
    }
    // If variables were captured in our path to match, they will be merged with our variable list provided with $argv
    if (is_callable($this->closure)) {
        $argv = array_merge($argv, $matches);
        call_user_func_array($this->closure, $argv);
        return $this;
    }
    return $this;
}