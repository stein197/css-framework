import java.lang.ArrayIndexOutOfBoundsException;
import java.util.HashMap;

public class ConsoleArgs{

	public final String[] args;

	private HashMap<String, String> list = new HashMap<>();

	public ConsoleArgs(String ...args){
		this.args = args;
		this.parse();
	}

	public String get(String name){
		return this.list.get(name);
	}

	public boolean exists(String name){
		return this.list.get(name) != null;
	}

	private void parse(){
		String current;
		String next;
		String key;
		String value;
		for(int i = 0; i < this.args.length; i++){
			current = this.args[i];
			next = getNext(i);
			if(isKeyname(current)){
				key = current.substring(1);
				value = isKeyname(next) ? "" : next;
				this.list.put(key, value);
			} else {
				continue;
			}
		}
	}

	private String getNext(int i){
		try{
			return this.args[i + 1];
		} catch (ArrayIndexOutOfBoundsException ex){
			return "";
		}
	}

	private boolean isKeyname(String name){
		return name.length() > 0 && name.charAt(0) == '-';
	}
}