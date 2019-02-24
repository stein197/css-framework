import java.util.ArrayList;
import java.text.ParseException;

public class Main{
	public static void main(String ...args) throws Exception{
		ConsoleArgs a = new ConsoleArgs(args);
		try{
			BracketParser.BracePair braces;
			switch(a.get("b")){
				case "{}":
					braces = new BracketParser.BracePair(BracketParser.BRACE_CURVE);
					break;
				case "[]":
					braces = new BracketParser.BracePair(BracketParser.BRACE_SQUARE);
					break;
				case "()":
					braces = new BracketParser.BracePair(BracketParser.BRACE_ROUND);
					break;
				case "<>":
					braces = new BracketParser.BracePair(BracketParser.BRACE_CORNER);
					break;
				default:
					throw new ParseException("", 1);
			}
			BracketParser parser = new BracketParser(a.get("m"), Byte.parseByte(a.get("d")));
			parser.brace = braces;
			try{
				ArrayList<String> list = parser.parse();
				for(String brace : list)
					System.out.println(brace);
			} catch (ParseException ex){
				System.out.println(ex.getMessage());
			}
		} catch (ParseException ex){
			System.out.println("There is no " + a.get("b") + " brace");
		}
	}
}