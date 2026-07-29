mvn clean package
cp build/lido_java21.war $(brew --prefix tomcat@11)/libexec/webapps/lido.war
$(brew --prefix tomcat@11)/bin/catalina run
# catalina run