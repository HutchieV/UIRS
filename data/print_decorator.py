from datetime import datetime

class PrintDecorator():
  """
  Contains a print decorator for printing time, thread/class, and debug 
  messages.
  """

  def __init__(self, sid=None, pd=False):
    if sid:
      self.script_id = "[{}]".format(sid)
    else:
      self.script_id = sid
    self.print_debug = pd


  def get_time_str(self, v):
    if v:
      return "[{}]".format(datetime.now().strftime('%H:%M:%S'))
    else:
      return ""

    
  def get_sid(self, v):
    if v:
      return self.script_id
    else:
      return ""


  def print_decorator(self, func):
    """
    Decorator used to amend the current time to the
    start of all calls to print()
    """
    def wrapped_func(*args,**kwargs):
      debug_id    = ""
      start_chars = ""
      print_time  = True
      print_sid   = True

      if "debug" in kwargs:
        del kwargs["debug"]
        if self.print_debug:
          debug_id = " *** debug ***"
        else:
          return
      if "time" in kwargs:
        print_time = kwargs["time"]
        del kwargs["time"]
      if "start" in kwargs:
        start_chars = kwargs["start"]
        del kwargs["start"]
      if "title" in kwargs:
        print_sid = kwargs["title"]
        del kwargs["title"]

      return func("{}{}{}{}".format(start_chars, 
                                    self.get_time_str(print_time),
                                    self.get_sid(print_sid), 
                                    debug_id),*args,**kwargs)
    return wrapped_func

  def set_debug(self, debug):
    """
    Sets whether or not this class prints debug messages.
    """
    self.print_debug = debug

  def get_debug(self):
    return self.print_debug